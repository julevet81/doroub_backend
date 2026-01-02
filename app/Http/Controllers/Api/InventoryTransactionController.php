<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\InventoryTransaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{

    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('الداخل للمخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        // جميع عمليات الإدخال
        $transactions = InventoryTransaction::where('transaction_type', 'in')
            ->with(['donor', 'assistanceItems', 'project'])
            ->get();

        // الإحصاءات
        $stats = [
            // عدد المتبرعين (بدون تكرار)
            'donors_count' => InventoryTransaction::where('transaction_type', 'in')
                ->whereNotNull('donor_id')
                ->distinct('donor_id')
                ->count('donor_id'),

            // عدد كل التحويلات من الخزينة (in)
            'total_in_transactions' => InventoryTransaction::where('transaction_type', 'in')->count(),

            // عدد التحويلات الموجهة إلى المخزون
            'to_inventory_count' => InventoryTransaction::where('transaction_type', 'in')
                ->where('orientation', 'inventory')
                ->count(),

            // عدد التحويلات الموجهة إلى المشاريع
            'to_projects_count' => InventoryTransaction::where('transaction_type', 'in')
                ->where('orientation', 'project')
                ->count(),
        ];

        return response()->json([
            'data' => $transactions,
            'statistics' => $stats
        ], 200);
    }


    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('الداخل للمخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'transaction_date' => 'required|date',
            'orientation' => 'nullable|string',
            'notes' => 'nullable|string',
            
            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'assistanceItems.*.quantity' => 'required|numeric|min:1',
        ]);

        $transaction = DB::transaction(function () use ($validated) {

            $transaction = InventoryTransaction::create([
                'transaction_type' => 'in',
                'donor_id' => $validated['donor_id'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'orientation' => $validated['orientation'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['assistanceItems'] as $row) {

                TransactionItem::create([
                    'inventory_transaction_id' => $transaction->id,
                    'assistance_item_id' => $row['assistance_item_id'],
                    'quantity' => $row['quantity'],
                ]);

                AssistanceItem::where('id', $row['assistance_item_id'])
                    ->increment('quantity_in_stock', $row['quantity']);
            }

            return $transaction;
        });

        return response()->json([
            'message' => 'تم تسجيل عملية الإدخال بنجاح',
            'data' => [
                'id' => $transaction->id,
                'transaction_type' => $transaction->transaction_type,
                'donor_id' => $transaction->donor_id,
                'transaction_date' => $transaction->transaction_date,
                'orientation' => $transaction->orientation,
                'notes' => $transaction->notes,
                'assistanceItems' => $transaction->assistanceItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'category' => $item->assistanceCategory?->name,
                        'quantity' => $item->pivot->quantity,
                    ];
                }),
            ],
        ], 201);
    }

    public function show(InventoryTransaction $inventoryTransaction)
    {
        if (!Auth::user() || !Auth::user()->can('الداخل للمخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        if ($inventoryTransaction->transaction_type != 'in') {
            return response()->json([
                'message' => 'هذه العملية ليست إدخال للمخزون'
            ], 400);
        }
        return response()->json([
            'data' => $inventoryTransaction->load([
                'donor',
                'assistanceItems'
            ])
        ], 200);
    }

    public function update(Request $request, InventoryTransaction $inventoryTransaction)
    {
        if (!Auth::user() || !Auth::user()->can('الداخل للمخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        if ($inventoryTransaction->transaction_type != 'in') {
            return response()->json([
                'message' => 'هذه العملية ليست إدخال للمخزون'
            ], 400);
        }
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'transaction_date' => 'sometimes|date',
            'orientation' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $inventoryTransaction->update($validated);

        return response()->json([
            'message' => 'تم تحديث العملية بنجاح',
            'data' => $inventoryTransaction
        ], 200);
    }

    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        if (!Auth::user() || !Auth::user()->can('الداخل للمخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        if ($inventoryTransaction->transaction_type != 'in') {
            return response()->json([
                'message' => 'هذه العملية ليست إدخال للمخزون'
            ], 400);
        }
        DB::transaction(function () use ($inventoryTransaction) {

            foreach ($inventoryTransaction->assistanceItems as $item) {
                AssistanceItem::where('id', $item->assistance_item_id)
                    ->decrement('quantity_in_stock', $item->pivot->quantity);
            }

            $inventoryTransaction->assistanceItems()->delete();
            $inventoryTransaction->delete();
        });

        return response()->json([
            'message' => 'تم حذف العملية بنجاح'
        ], 200);
    }
}

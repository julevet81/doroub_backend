<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\Donor;
use App\Models\FinancialTransaction;
use App\Models\InventoryTransaction;
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
            ->with(['assistanceItems', 'project'])
            ->get();

        $count = Donor::count();

        // الإحصاءات
        $stats = [

            'donors_count' => $count,

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
            'from_type' => 'nullable|string|in:donor,treasury',
            'expected_amount' => 'required_if:from_type,treasury|numeric|min:0',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'donor_id' => 'nullable|exists:donors,id',
            'transaction_date' => 'required|date',
            'orientation' => 'nullable|string|in:inventory,project',
            'notes' => 'nullable|string',

            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'nullable|exists:assistance_items,id',
            'assistanceItems.*.name' => 'required_without:assistanceItems.*.assistance_item_id|string|max:255',
            'assistanceItems.*.quantity' => 'required|numeric|min:1',
        ]);

        $transaction = DB::transaction(function () use ($validated, $request) {

            /*
        |--------------------------------------------------------------------------
        | 🔹 إذا كان المصدر من الخزينة
        |--------------------------------------------------------------------------
        */
            if (($validated['from_type'] ?? null) === 'treasury') {

                // 🔸 جلب آخر رصيد
                $lastTransaction = FinancialTransaction::orderByDesc('id')->first();
                $currentBalance = $lastTransaction?->new_balance ?? 0;

                if ($currentBalance < $validated['expected_amount']) {
                    throw new \Exception('الرصيد غير كافي في الخزينة');
                }

                // 🔸 رفع صورة الفاتورة
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $attachmentPath = $request->file('attachment')
                        ->store('financial_attachments', 'public');
                }

                $newBalance = $currentBalance - $validated['expected_amount'];

                // 🔸 تسجيل عملية خصم
                FinancialTransaction::create([
                    'amount' => $validated['expected_amount'],
                    'transaction_type' => 'expense',
                    'orientation' => 'treasury',
                    'payment_method' => 'cash',
                    'attachment' => $attachmentPath,
                    'previous_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'description' => 'شراء مواد للمخزون',
                    'transaction_date' => $validated['transaction_date'],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 🔹 إنشاء عملية إدخال المخزون
        |--------------------------------------------------------------------------
        */
            $inventoryTransaction = InventoryTransaction::create([
                'transaction_type' => 'in',
                'donor_id' => $validated['donor_id'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'orientation' => $validated['orientation'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['assistanceItems'] as $itemData) {

                if (!empty($itemData['assistance_item_id'])) {
                    $item = AssistanceItem::find($itemData['assistance_item_id']);
                } else {
                    $barcode = random_int(1000000000, 9999999999);

                    $item = AssistanceItem::create([
                        'name' => $itemData['name'],
                        'quantity_in_stock' => 0,
                        'code' => $barcode,
                    ]);
                }

                $inventoryTransaction->assistanceItems()->attach($item->id, [
                    'quantity' => $itemData['quantity']
                ]);

                $item->increment('quantity_in_stock', $itemData['quantity']);
            }

            return $inventoryTransaction->load('assistanceItems.assistanceCategory');
        });

        return response()->json([
            'message' => 'تم تسجيل عملية الإدخال بنجاح',
            'data' => $transaction
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
            'orientation' => 'nullable|string|in:inventory,project',
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

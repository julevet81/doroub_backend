<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\InventoryTransaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryOutController extends Controller
{
    /**
     * Display a listing of OUT transactions.
     */
    public function index()
    {
        $transactions = InventoryTransaction::where('transaction_type', 'out')
            ->with(['orientation', 'assistanceItems.assistanceItem'])
            ->get();

        return response()->json([
            'data' => $transactions
        ], 200);
    }

    /**
     * Store a newly created OUT transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'orientation' => 'nullable|string',
            'notes' => 'nullable|string',

            'project_id' => 'nullable|exists:projects,id',
            'beneficiary_id' => 'nullable|exists:beneficiaries,id',

            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'assistanceItems.*.quantity' => 'required|integer|min:1',
        ]);

        $transaction = DB::transaction(function () use ($validated) {

            $transaction = InventoryTransaction::create([
                'transaction_type' => 'out',
                'transaction_date' => $validated['transaction_date'],
                'orientation' => $validated['orientation'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'beneficiary_id' => $validated['beneficiary_id'] ?? null,
            ]);

            foreach ($validated['items'] as $row) {

                $item = AssistanceItem::findOrFail($row['assistance_item_id']);

                // حماية من نفاد المخزون
                if ($item->quantity_in_stock < $row['quantity']) {
                    throw new \Exception("الكمية غير كافية للصنف: {$item->name}");
                }

                TransactionItem::create([
                    'inventory_transaction_id' => $transaction->id,
                    'assistance_item_id' => $row['assistance_item_id'],
                    'quantity' => $row['quantity'],
                ]);

                // إنقاص المخزون
                $item->decrement('quantity_in_stock', $row['quantity']);
            }

            return $transaction;
        });

        return response()->json([
            'message' => 'تم تسجيل عملية الإخراج بنجاح',
            'data' => $transaction->load('assistanceItems.assistanceItem')
        ], 201);
    }

    /**
     * Display the specified OUT transaction.
     */
    public function show(InventoryTransaction $inventoryTransaction)
    {
        if ($inventoryTransaction->transaction_type !== 'out') {
            return response()->json([
                'message' => 'هذه العملية ليست إخراج مخزون'
            ], 400);
        }

        return response()->json([
            'data' => $inventoryTransaction->load([
                'assistanceItems.assistance_item',
                'project',
                'beneficiary'
            ])
        ], 200);
    }

    /**
     * Remove the specified OUT transaction.
     */
    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        if ($inventoryTransaction->transaction_type !== 'out') {
            return response()->json([
                'message' => 'عملية غير صالحة'
            ], 400);
        }

        DB::transaction(function () use ($inventoryTransaction) {

            foreach ($inventoryTransaction->items as $item) {
                AssistanceItem::where('id', $item->assistance_item_id)
                    ->increment('quantity_in_stock', $item->quantity);
            }

            $inventoryTransaction->assistanceItems()->delete();
            $inventoryTransaction->delete();
        });

        return response()->json([
            'message' => 'تم حذف عملية الإخراج بنجاح'
        ], 200);
    }
}

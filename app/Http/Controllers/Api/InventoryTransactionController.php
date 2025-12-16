<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\InventoryTransaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    
    public function index()
    {
        $transactions = InventoryTransaction::where('transaction_type', 'in')
            ->with(['donor', 'orientation', 'assistanceItems.assistanceItem'])
            ->get();

        return response()->json([
            'data' => $transactions
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'transaction_date' => 'required|date',
            'orientation' => 'nullable|string',
            'notes' => 'nullable|string',

            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'assistanceItems.*.quantity' => 'required|integer|min:1',
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
            'data' => $transaction->load('assistanceItems.assistanceItem')
        ], 201);
    }

    public function show(InventoryTransaction $inventoryTransaction)
    {
        return response()->json([
            'data' => $inventoryTransaction->load([
                'donor',
                'orientation',
                'assistanceItems.assistanceItem'
            ])
        ], 200);
    }

    public function update(Request $request, InventoryTransaction $inventoryTransaction)
    {
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:donors,id',
            'transaction_date' => 'required|date',
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
        DB::transaction(function () use ($inventoryTransaction) {

            foreach ($inventoryTransaction->items as $item) {
                AssistanceItem::where('id', $item->assistance_item_id)
                    ->decrement('quantity_in_stock', $item->quantity);
            }

            $inventoryTransaction->items()->delete();
            $inventoryTransaction->delete();
        });

        return response()->json([
            'message' => 'تم حذف العملية بنجاح'
        ], 200);
    }
}

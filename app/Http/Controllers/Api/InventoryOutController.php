<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\InventoryTransaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryOutController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('الخارج من المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $transactions = InventoryTransaction::query()
            ->where('transaction_type', 'out')
            ->select('id', 'orientation_out', 'transaction_date')
            ->orderByDesc('transaction_date')
            ->get();

        $stats = [
            'to_projects' => InventoryTransaction::where('transaction_type', 'out')
                ->whereNotNull('project_id')
                ->count(),

            'to_beneficiaries' => InventoryTransaction::where('transaction_type', 'out')
                ->whereNotNull('beneficiary_id')
                ->count(),
        ];

        return response()->json([
            'data' => $transactions,
            'statistics' => $stats
        ], 200);
    }


    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('الخارج من المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'orientation_out' => 'required|in:project,beneficiary,other',
            'notes' => 'nullable|string',

            'project_id' => 'required_if:orientation,project|nullable|exists:projects,id',
            'beneficiary_id' => 'required_if:orientation,beneficiary|nullable|exists:beneficiaries,id',

            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'assistanceItems.*.quantity' => 'required|integer|min:1',
        ]);

        $transaction = DB::transaction(function () use ($validated) {

            $transaction = InventoryTransaction::create([
                'transaction_type' => 'out',
                'transaction_date' => $validated['transaction_date'],
                'orientation_out' => $validated['orientation_out'],
                'notes' => $validated['notes'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'beneficiary_id' => $validated['beneficiary_id'] ?? null,
            ]);

            foreach ($validated['assistanceItems'] as $row) {

                $item = AssistanceItem::lockForUpdate()->findOrFail($row['assistance_item_id']);

                if ($item->quantity_in_stock < $row['quantity']) {
                    throw new \Exception("الكمية غير كافية للصنف: {$item->name}");
                }

                $transaction->assistanceItems()->attach(
                    $item->id,
                    ['quantity' => $row['quantity']]
                );

                $item->decrement('quantity_in_stock', $row['quantity']);
            }

            return $transaction;
        });

        return response()->json([
            'message' => 'تم تسجيل عملية الإخراج بنجاح',
            'data' => $transaction->load('assistanceItems:id,name')
        ], 201);
    }

    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('الخارج من المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $inventoryTransaction = InventoryTransaction::where('id', $id)
            ->where('transaction_type', 'out')
            ->with([
                'project:id,name',
                'beneficiary:id,name',
                'assistanceItems:id,name'
            ])
            ->first();

        if (! $inventoryTransaction) {
            return response()->json([
                'message' => 'عملية الإخراج غير موجودة'
            ], 404);
        }

        return response()->json([
            'data' => $inventoryTransaction
        ], 200);
    }


    public function update(Request $request,$id)
    {
        if (!Auth::user() || !Auth::user()->can('الخارج من المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $inventoryTransaction = InventoryTransaction::where('id', $id)
            ->where('transaction_type', 'out')
            ->with([
                'project:id,name',
                'beneficiary:id,name',
                'assistanceItems:id,name'
            ])
            ->first();
        if (! $inventoryTransaction) {
            return response()->json([
                'message' => 'عملية الإخراج غير موجودة'
            ], 404);
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'orientation_out' => 'required|in:project,beneficiary,other',
            'notes' => 'nullable|string',

            'assistanceItems' => 'required|array|min:1',
            'assistanceItems.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'assistanceItems.*.quantity' => 'required|integer|min:1',
        ]);

        $transaction = DB::transaction(function () use ($validated, $inventoryTransaction) {

            /** 1️⃣ إعادة الكميات القديمة للمخزون */
            foreach ($inventoryTransaction->assistanceItems as $oldItem) {
                $oldItem->increment(
                    'quantity_in_stock',
                    $oldItem->pivot->quantity
                );
            }

            /** 2️⃣ حذف العناصر القديمة */
            $inventoryTransaction->assistanceItems()->detach();

            /** 3️⃣ تحديث بيانات المعاملة */
            $inventoryTransaction->update([
                'transaction_date' => $validated['transaction_date'],
                'orientation_out' => $validated['orientation_out'],
                'notes' => $validated['notes'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'beneficiary_id' => $validated['beneficiary_id'] ?? null,
            ]);

            /** 4️⃣ إدخال العناصر الجديدة */
            foreach ($validated['assistanceItems'] as $row) {

                $item = AssistanceItem::lockForUpdate()
                    ->findOrFail($row['assistance_item_id']);

                if ($item->quantity_in_stock < $row['quantity']) {
                    throw new \Exception("الكمية غير كافية للصنف: {$item->name}");
                }

                $inventoryTransaction->assistanceItems()->attach(
                    $item->id,
                    ['quantity' => $row['quantity']]
                );

                $item->decrement('quantity_in_stock', $row['quantity']);
            }

            return $inventoryTransaction;
        });

        return response()->json([
            'message' => 'تم تحديث عملية الإخراج بنجاح',
            'data' => $transaction->load('assistanceItems:id,name')
        ], 200);
    }


    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->can('الخارج من المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $inventoryTransaction = InventoryTransaction::where('id', $id)
            ->where('transaction_type', 'out')
            ->with([
                'project:id,name',
                'beneficiary:id,name',
                'assistanceItems:id,name'
            ])
            ->first();

        if (! $inventoryTransaction) {
            return response()->json([
                'message' => 'عملية الإخراج غير موجودة'
            ], 404);
        }

        DB::transaction(function () use ($inventoryTransaction) {

            foreach ($inventoryTransaction->assistanceItems as $item) {
                AssistanceItem::where('id', $item->assistance_item_id)
                    ->increment('quantity_in_stock', (int)$item->quantity);
            }

            $inventoryTransaction->assistanceItems()->delete();
            $inventoryTransaction->delete();
        });

        return response()->json([
            'message' => 'تم حذف عملية الإخراج بنجاح'
        ], 200);
    }
}

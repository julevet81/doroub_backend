<?php

namespace App\Http\Controllers\Api;

use App\Models\Benefice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BeneficeController extends Controller
{
    public function index()
    {
        try {
            if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
                return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
            }

            $benefices = Benefice::with(['beneficiary', 'items'])->paginate(20);

            return response()->json(['data' => $benefices], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'حدث خطأ أثناء جلب البيانات', 'error' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
                return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
            }

            $validated = $request->validate([
                'beneficiary_id' => 'required|exists:beneficiaries,id',
                'type' => 'required|in:financial,material',
                'amount' => 'required_if:type,financial|nullable|numeric|min:0',
                'items' => 'required_if:type,material|array',
                'items.*.assistance_item_id' => 'required_if:type,material|exists:assistance_items,id',
                'items.*.quantity' => 'required_if:type,material|integer|min:1',
            ]);

            $benefice = Benefice::create([
                'beneficiary_id' => $request->beneficiary_id,
                'type' => $request->type,
                'amount' => $request->type === 'financial' ? $request->amount : null,
            ]);

            if ($request->type === 'material' && $request->has('items')) {
                $syncData = [];
                foreach ($request->items as $item) {
                    $syncData[$item['assistance_item_id']] = ['quantity' => $item['quantity']];
                }
                $benefice->items()->sync($syncData);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الإستفادة بنجاح.',
                'data' => $benefice->load(['beneficiary', 'items'])
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'فشل إنشاء الإستفادة', 'error' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
                return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
            }

            $benefice = Benefice::with(['beneficiary', 'items'])->findOrFail($id);

            return response()->json(['data' => $benefice], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'فشل عرض الإستفادة', 'error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
                return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
            }

            $benefice = Benefice::findOrFail($id);

            $request->validate([
                'beneficiary_id' => 'required|exists:beneficiaries,id',
                'type' => 'required|in:financial,material',
                'amount' => 'required_if:type,financial|nullable|numeric|min:0',
                'items' => 'required_if:type,material|array',
                'items.*.assistance_item_id' => 'required_if:type,material|exists:assistance_items,id',
                'items.*.quantity' => 'required_if:type,material|integer|min:1',
            ]);

            $benefice->update([
                'beneficiary_id' => $request->beneficiary_id,
                'type' => $request->type,
                'amount' => $request->type === 'financial' ? $request->amount : null,
            ]);

            if ($request->type === 'material') {
                $syncData = [];
                foreach ($request->items as $item) {
                    $syncData[$item['assistance_item_id']] = ['quantity' => $item['quantity']];
                }
                $benefice->items()->sync($syncData);
            } else {
                $benefice->items()->detach();
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الإستفادة بنجاح.',
                'data' => $benefice->load(['beneficiary', 'items'])
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'فشل تحديث الإستفادة', 'error' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $benefice = Benefice::findOrFail($id);
            $benefice->delete();

            return response()->json(['message' => 'تم حذف الإستفادة بنجاح.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'فشل حذف الإستفادة', 'error' => $th->getMessage()], 500);
        }
    }
}

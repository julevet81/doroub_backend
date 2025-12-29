<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demond;
use App\Models\AssistanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemondController extends Controller
{
    /**
     * Display a listing of demonds.
     */
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('الطلبات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $demonds = Demond::with(['beneficiary', 'assistanceItems'])->paginate(20);

        return response()->json([
            'data' => $demonds
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('الطلبات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'demand_date' => 'required|date',
            'attachement' => 'nullable|file|max:2048',
            'description' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.assistance_item_id' => 'required|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('attachement')) {
                $validated['attachement'] =
                    $request->file('attachement')->store('demonds', 'public');
            }

            $validated['treated_by'] = Auth::id();

            // إنشاء الطلب
            $demond = Demond::create($validated);

            // إضافة العناصر المطلوبة
            foreach ($validated['items'] as $item) {
                $demond->items()->attach(
                    $item['assistance_item_id'],
                    ['quantity' => $item['quantity']]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => $demond->load(['beneficiary', 'items'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('الطلبات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $demond = Demond::with([
            'beneficiary',
            'items',
            'items.pivot',
            'treatedBy'
        ])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $demond->id,
                'beneficiary' => $demond->beneficiary,
                'demand_date' => $demond->demand_date,
                'description' => $demond->description,
                'attachement_url' => $demond->attachement
                    ? asset('storage/' . $demond->attachement)
                    : null,
                'treated_by' => $demond->treatedBy,
                'items' => $demond->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->pivot->quantity,
                    ];
                }),
                'created_at' => $demond->created_at,
            ]
        ], 200);
    }

    

    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('الطلبات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $demond = Demond::findOrFail($id);

        $validated = $request->validate([
            'beneficiary_id' => 'sometimes|exists:beneficiaries,id',
            'demand_date' => 'sometimes|date',
            'attachement' => 'nullable|file|max:2048',
            'description' => 'nullable|string',

            'items' => 'nullable|array|min:1',
            'items.*.assistance_item_id' => 'required_with:items|exists:assistance_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        DB::beginTransaction();

        try {

            // تحديث الملف المرفق
            if ($request->hasFile('attachement')) {

                // حذف الملف القديم
                if ($demond->attachement) {
                    Storage::disk('public')->delete($demond->attachement);
                }

                $validated['attachement'] =
                    $request->file('attachement')->store('demonds', 'public');
            }

            // المستخدم الذي عدل الطلب
            $validated['treated_by'] = Auth::id();

            // تحديث بيانات الطلب
            $demond->update($validated);

            /**
             * تحديث العناصر
             * sync سيقوم بـ:
             * - تحديث الكمية
             * - إضافة عناصر جديدة
             * - حذف العناصر غير المرسلة
             */
            if (isset($validated['items'])) {

                $items = [];

                foreach ($validated['items'] as $item) {
                    $items[$item['assistance_item_id']] = [
                        'quantity' => $item['quantity']
                    ];
                }

                $demond->items()->sync($items);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الطلب بنجاح',
                'data' => $demond->load(['beneficiary', 'items', 'treatedBy'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->can('الطلبات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $demond = Demond::findOrFail($id);

        DB::beginTransaction();

        try {

            // حذف الملف المرفق من التخزين
            if ($demond->attachement) {
                Storage::disk('public')->delete($demond->attachement);
            }

            /**
             * بسبب onDelete('cascade')
             * سيتم حذف السجلات من demonded_items تلقائياً
             */
            $demond->delete();

            DB::commit();

            return response()->json([
                'message' => 'تم حذف الطلب بنجاح'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'فشل حذف الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

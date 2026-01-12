<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        
        $projects = Project::with(['items', 'volunteers'])->get();

        return response()->json([
            'data' => $projects
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',

            'items' => 'required|array',
            'items.*.id' => 'required|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',

            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'required|exists:volunteers,id',
            'volunteers.*.position' => 'nullable|string|max:255',
        ]);

        try {
            $project = DB::transaction(function () use ($validated) {

                // 🔹 التحقق من الكميات قبل إنشاء المشروع
                foreach ($validated['items'] as $item) {
                    $assistanceItem = AssistanceItem::lockForUpdate()->find($item['id']);

                    if ($item['quantity'] > $assistanceItem->quantity_in_stock) {
                        throw ValidationException::withMessages([
                            'items' => "الكمية غير كافية للعنصر: {$assistanceItem->name}"
                        ]);
                    }
                }

                $project = Project::create([
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? null,
                    'budget' => $validated['budget'] ?? null,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 🔹 ربط العناصر مع تحديد rest_in_project = quantity
                foreach ($validated['items'] as $item) {
                    $project->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'rest_in_project' => $item['quantity'], // 👈 القيمة الجديدة
                    ]);

                    // 🔹 تنزيل الكمية من المخزون
                    AssistanceItem::where('id', $item['id'])
                        ->decrement('quantity_in_stock', $item['quantity']);
                }

                // 🔹 ربط المتطوعين إن وجدوا
                if (!empty($validated['volunteers'])) {
                    foreach ($validated['volunteers'] as $vol) {
                        $project->volunteers()->attach($vol['id'], [
                            'position' => $vol['position'] ?? null
                        ]);
                    }
                }

                return $project;
            });

            return response()->json([
                'message' => 'تم إنشاء المشروع بنجاح',
                'data' => $project->load(['items', 'volunteers'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'فشل إنشاء المشروع',
                'errors' => $e->errors()
            ], 422);
        }
    }



    public function show(Project $project)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        return response()->json([
            'data' => $project->load(['items', 'volunteers'])
        ], 200);
    }

    public function update(Request $request, Project $project)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'nullable|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',

            'items' => 'required|array',
            'items.*.id' => 'required|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',

            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'required|exists:volunteers,id',
            'volunteers.*.position' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $project) {

                /** 1️⃣ جلب الكميات القديمة مع rest_in_project */
                $oldItems = $project->items()->withPivot('quantity', 'rest_in_project')->get();

                /** 2️⃣ إرجاع الكميات القديمة للمخزون */
                foreach ($oldItems as $old) {
                    AssistanceItem::where('id', $old->id)
                        ->increment('quantity_in_stock', $old->pivot->quantity);
                }

                /** 3️⃣ التحقق من توفر الكميات الجديدة */
                foreach ($validated['items'] as $item) {
                    $assistanceItem = AssistanceItem::lockForUpdate()->find($item['id']);

                    if ($item['quantity'] > $assistanceItem->quantity_in_stock) {
                        throw ValidationException::withMessages([
                            'items' => "الكمية غير كافية للعنصر {$assistanceItem->name} (المتاح: {$assistanceItem->quantity_in_stock})"
                        ]);
                    }
                }

                /** 4️⃣ تحديث بيانات المشروع */
                $project->update([
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? null,
                    'amount' => $validated['amount'] ?? null,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                /** 5️⃣ فصل العناصر والمتطوعين */
                $project->items()->detach();
                $project->volunteers()->detach();

                /** 6️⃣ ربط العناصر من جديد مع rest_in_project */
                foreach ($validated['items'] as $item) {
                    $project->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],
                        'rest_in_project' => $item['quantity'], // 👈 تحديث القيمة الجديدة
                    ]);

                    AssistanceItem::where('id', $item['id'])
                        ->decrement('quantity_in_stock', $item['quantity']);
                }

                /** 7️⃣ ربط المتطوعين */
                if (!empty($validated['volunteers'])) {
                    foreach ($validated['volunteers'] as $vol) {
                        $project->volunteers()->attach($vol['id'], [
                            'position' => $vol['position'] ?? null
                        ]);
                    }
                }
            });

            return response()->json([
                'message' => 'تم تحديث المشروع بنجاح',
                'data' => $project->load(['items', 'volunteers'])
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'فشل تحديث المشروع',
                'errors' => $e->errors()
            ], 422);
        }
    }


    public function destroy(Project $project)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $project->delete();

        return response()->json([
            'message' => 'تم حذف المشروع بنجاح'
        ], 200);
    }
}

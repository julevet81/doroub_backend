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
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:social,economic,health,education,other',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:planned,ongoing,completed,canceled',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.id' => 'required|integer|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $project = Project::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'budget' => $validated['budget'],
                'remaining_amount' => $validated['budget'], // المبلغ المتبقي = الميزانية
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $project->items()->attach($item['id'], [
                        'quantity' => $item['quantity'],       // الكمية المحجوزة
                        'rest_in_project' => $item['quantity'] // المتبقي يبدأ بنفس القيمة
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء المشروع بنجاح',
                'data' => $project->load('items')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'فشل إنشاء المشروع',
                'error' => $e->getMessage()
            ], 500);
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string',
            'type' => 'nullable|string|in:social,economic,health,education,other',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:planned,ongoing,completed,canceled',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.id' => 'required|integer|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $project = Project::findOrFail($id);

            // تحديث ميزانية المشروع مع الحفاظ على remaining_amount الحالي
            if (isset($validated['budget'])) {
                $difference = $validated['budget'] - $project->budget;

                // إذا زادت الميزانية -> نزيد remaining_amount
                if ($difference > 0) {
                    $project->remaining_amount += $difference;
                } else {
                    // إذا نقصت الميزانية -> لازم remaining_amount >= |difference|
                    if ($project->remaining_amount < abs($difference)) {
                        throw new \Exception("لا يمكن تخفيض الميزانية لأن remaining_amount لا يسمح بذلك");
                    }
                    $project->remaining_amount += $difference;
                }
            }

            $project->update($validated);

            // تحديث المواد
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $existing = $project->items()->where('assistance_item_id', $item['id'])->first();

                    if ($existing) {
                        // تحديث الكمية المحجوزة مع التأكد أن rest_in_project لا تتجاوزها
                        if ($existing->pivot->rest_in_project > $item['quantity']) {
                            throw new \Exception("لا يمكن وضع كمية أقل من rest_in_project الحالية");
                        }

                        $project->items()->updateExistingPivot($item['id'], [
                            'quantity' => $item['quantity'],
                        ]);
                    } else {
                        // إضافة مادة جديدة
                        $project->items()->attach($item['id'], [
                            'quantity' => $item['quantity'],
                            'rest_in_project' => $item['quantity']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تعديل المشروع بنجاح',
                'data' => $project->load('items')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'فشل تعديل المشروع',
                'error' => $e->getMessage()
            ], 500);
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

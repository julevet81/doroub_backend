<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['assistances', 'volunteers'])->get();

        return response()->json([
            'data' => $projects
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',

            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',

            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'required|exists:volunteers,id',
            'volunteers.*.position' => 'nullable|string|max:255',
        ]);

        $project = DB::transaction(function () use ($validated) {

            $project = Project::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'budget' => $validated['budget'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $project->items()->attach($item['item_id'], [
                    'quantity' => $item['quantity']
                ]);

                AssistanceItem::where('id', $item['item_id'])
                    ->decrement('quantity_in_stock', $item['quantity']);
            }

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
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return response()->json([
            'data' => $project->load(['items', 'volunteers'])
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'nullable|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',

            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:assistance_items,id',
            'items.*.quantity' => 'required|integer|min:1',

            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'required|exists:volunteers,id',
            'volunteers.*.position' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $project) {

            // إعادة الكمية القديمة للمخزون
            foreach ($project->items as $old) {
                AssistanceItem::where('id', $old->id)
                    ->increment('quantity_in_stock', $old->pivot->quantity);
            }

            $project->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $project->items()->detach();
            $project->volunteers()->detach();

            foreach ($validated['items'] as $item) {
                $project->items()->attach($item['item_id'], [
                    'quantity' => $item['quantity']
                ]);

                AssistanceItem::where('id', $item['item_id'])
                    ->decrement('quantity_in_stock', $item['quantity']);
            }

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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'تم حذف المشروع بنجاح'
        ], 200);
    }
}

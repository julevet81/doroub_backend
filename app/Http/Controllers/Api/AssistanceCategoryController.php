<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceCategory;
use Illuminate\Http\Request;

class AssistanceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => AssistanceCategory::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = AssistanceCategory::create($validated);

        return response()->json([
            'message' => 'Assistance category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AssistanceCategory $assistanceCategory)
    {
        return response()->json([
            'data' => $assistanceCategory
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssistanceCategory $assistanceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $assistanceCategory->update($validated);

        return response()->json([
            'message' => 'Assistance category updated successfully',
            'data' => $assistanceCategory
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssistanceCategory $assistanceCategory)
    {
        $assistanceCategory->delete();

        return response()->json([
            'message' => 'Assistance category deleted successfully'
        ], 200);
    }
}

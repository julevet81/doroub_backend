<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BeneficiaryCategory;
use Illuminate\Http\Request;

class BeneficiaryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = BeneficiaryCategory::all();

        return response()->json([
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = BeneficiaryCategory::create($validated);

        return response()->json([
            'message' => 'تم إنشاء فئة المستفيدين بنجاح',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BeneficiaryCategory $beneficiaryCategory)
    {
        return response()->json([
            'data' => $beneficiaryCategory
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BeneficiaryCategory $beneficiaryCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_categories,name,' . $beneficiaryCategory->id,
            'description' => 'nullable|string',
        ]);

        $beneficiaryCategory->update($validated);

        return response()->json([
            'message' => 'تم تحديث فئة المستفيدين بنجاح',
            'data' => $beneficiaryCategory
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BeneficiaryCategory $beneficiaryCategory)
    {
        $beneficiaryCategory->delete();

        return response()->json([
            'message' => 'تم حذف فئة المستفيدين بنجاح'
        ], 200);
    }
}

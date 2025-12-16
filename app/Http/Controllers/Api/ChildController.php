<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // يمكنك إضافة فلترة هنا إذا كانت ضرورية
        $children = Child::with('beneficiary')->paginate(20);

        return response()->json([
            'data' => $children
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'gender' => 'nullable|string|max:50',
            'study_level' => 'nullable|string|max:255',
            'school' => 'nullable|string|max:255',
            'health_status' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $child = Child::create($validatedData);

        return response()->json([
            'message' => 'تم إضافة الطفل بنجاح.',
            'data' => $child
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Child $child)
    {
        return response()->json([
            'data' => $child
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Child $child)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'gender' => 'nullable|string|max:50',
            'study_level' => 'nullable|string|max:255',
            'school' => 'nullable|string|max:255',
            'health_status' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $child->update($validatedData);

        return response()->json([
            'message' => 'تم تحديث بيانات الطفل بنجاح.',
            'data' => $child
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Child $child)
    {
        $child->delete();

        return response()->json([
            'message' => 'تم حذف الطفل بنجاح.'
        ], 200);
    }
}

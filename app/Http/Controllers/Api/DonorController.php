<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceCategory;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('المتبرعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        return response()->json([
            'data' => Donor::with('assistanceCategory')->get()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('المتبرعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'activity' => 'required|string|unique:donors,activity',
            'phone' => 'required|string|max:20',
            'assistance_category_id' => 'nullable|exists:assistance_categories,id',
            'description' => 'nullable|string',
        ]);

        $donor = Donor::create($validated);

        return response()->json([
            'message' => 'تم إضافة المتبرع بنجاح',
            'data' => $donor->load('assistanceCategory')
        ], 201);
    }
    public function show(Donor $donor)
    {
        if (!Auth::user() || !Auth::user()->can('المتبرعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        return response()->json([
            'data' => $donor->load('assistanceCategory')
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Donor $donor)
    {
        if (!Auth::user() || !Auth::user()->can('المتبرعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'activity' => 'sometimes|string|unique:donors,activity,' . $donor->id,
            'phone' => 'required|string|max:20',
            'assistance_category_id' => 'sometimes|exists:assistance_categories,id',
            'description' => 'nullable|string',
        ]);

        $donor->update($validated);

        return response()->json([
            'message' => 'تم تحديث بيانات المتبرع بنجاح',
            'data' => $donor->load('assistanceCategory')
        ], 200);
    }
    public function destroy(Donor $donor)
    {
        if (!Auth::user() || !Auth::user()->can('المتبرعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        
        $donor->delete();

        return response()->json([
            'message' => 'تم حذف المتبرع بنجاح'
        ], 200);
    }

    /**
     * Optional: List assistance categories (helper endpoint)
     */
    public function categories()
    {
        return response()->json([
            'data' => AssistanceCategory::all()
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartnerInfo;
use Illuminate\Http\Request;

class PartnerInfoController extends Controller
{
    /**
     * Display a listing of partner infos.
     */
    public function index()
    {
        $partnerInfos = PartnerInfo::paginate(20);

        return response()->json([
            'data' => $partnerInfos
        ], 200);
    }

    /**
     * Store a newly created partner info.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'study_level' => 'nullable|in:none,primary,secondary,higher',
            'health_status' => 'nullable|string|max:255',
            'insured' => 'required|boolean',
            'income_source' => 'nullable|string|max:255',
        ]);

        $partnerInfo = PartnerInfo::create($validated);

        return response()->json([
            'message' => 'تم إنشاء معلومات الشريك بنجاح.',
            'data' => $partnerInfo
        ], 201);
    }

    /**
     * Display the specified partner info.
     */
    public function show($id)
    {
        $partnerInfo = PartnerInfo::findOrFail($id);

        return response()->json([
            'data' => $partnerInfo
        ], 200);
    }

    /**
     * Update the specified partner info.
     */
    public function update(Request $request, $id)
    {
        $partnerInfo = PartnerInfo::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'study_level' => 'nullable|in:none,primary,secondary,higher',
            'health_status' => 'nullable|string|max:255',
            'insured' => 'required|boolean',
            'income_source' => 'nullable|string|max:255',
        ]);

        $partnerInfo->update($validated);

        return response()->json([
            'message' => 'تم تحديث معلومات الشريك بنجاح.',
            'data' => $partnerInfo
        ], 200);
    }

    /**
     * Remove the specified partner info.
     */
    public function destroy($id)
    {
        $partnerInfo = PartnerInfo::findOrFail($id);
        $partnerInfo->delete();

        return response()->json([
            'message' => 'تم حذف معلومات الشريك بنجاح.'
        ], 200);
    }
}

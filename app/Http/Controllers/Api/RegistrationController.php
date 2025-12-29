<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    /**
     * Display a listing of registrations with optional filters.
     */
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $query = Registration::with('beneficiary');

        if ($request->district_id) {
            $query->whereRelation('beneficiary', 'district_id', $request->district_id);
        }

        if ($request->municipality_id) {
            $query->whereRelation('beneficiary', 'municipality_id', $request->municipality_id);
        }

        if ($request->city) {
            $query->whereRelation('beneficiary', 'city', 'LIKE', "%{$request->city}%");
        }

        if ($request->nbr_in_family) {
            $query->whereRelation('beneficiary', 'nbr_in_family', $request->nbr_in_family);
        }

        if ($request->social_status) {
            $query->whereRelation('beneficiary', 'social_status', $request->social_status);
        }

        $registrations = $query->latest()->paginate(20);

        return response()->json($registrations);
    }
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'status' => 'required|in:accepted,in_study,rejected',
            'notes' => 'nullable|string',
        ]);

        $registration = Registration::create($validated);

        return response()->json([
            'message' =>  'تم إنشاء التسجيل بنجاح',
            'data' => $registration
        ], 201);
    }
    public function show(Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $registration->load('beneficiary');

        return response()->json($registration);
    }
    public function update(Request $request, Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'status' => 'required|in:accepted,in_study,rejected',
            'notes' => 'nullable|string',
        ]);

        $registration->update($validated);

        return response()->json([
            'message' => 'تم تحديث التسجيل بنجاح',
            'data' => $registration
        ]);
    }
    public function destroy(Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        
        $registration->delete();

        return response()->json([
            'message' => 'تم حذف التسجيل بنجاح'
        ], 200);
    }
}

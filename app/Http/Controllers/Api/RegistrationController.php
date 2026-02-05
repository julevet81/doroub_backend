<?php

namespace App\Http\Controllers;

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

        $query = Registration::query();

        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->municipality_id) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->city) {
            $query->where('city', 'LIKE', "%{$request->city}%");
        }

        if ($request->nbr_in_family) {
            $query->where('nbr_in_family', $request->nbr_in_family);
        }

        if ($request->social_status) {
            $query->where('social_status', $request->social_status);
        }

        if ($request->insured !== null) {
            $query->where('insured', $request->insured);
        }

        $registrations = $query->latest()->paginate(20);

        return response()->json($registrations);
    }

    /**
     * Store a newly created registration.
     */
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'job' => 'nullable|string|max:255',
            'health_status' => 'nullable|string|max:255',
            'insured' => 'nullable|boolean',
            'social_status' => 'required|in:divorced,widowed,low_income,cancer_patient',
            'nbr_in_family' => 'nullable|integer|min:1',
            'nbr_studing' => 'nullable|integer|min:0',
            'house_status' => 'required|in:owned,rented,host,other',
            'district_id' => 'required|exists:districts,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'city' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',

            // الزوجة
            'first_name_of_wife' => 'nullable|string|max:255',
            'last_name_of_wife' => 'nullable|string|max:255',
            'date_of_birth_of_wife' => 'nullable|date',
            'birth_place_of_wife' => 'nullable|string|max:255',
            'job_of_wife' => 'nullable|string|max:255',
            'health_status_of_wife' => 'nullable|string|max:255',
            'is_wife_insured' => 'nullable|boolean',

            'notes' => 'nullable|string',
        ]);

        $registration = Registration::create($validated);

        return response()->json([
            'message' => 'تم إنشاء التسجيل بنجاح',
            'data' => $registration
        ], 201);
    }

    /**
     * Display the specified registration.
     */
    public function show(Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        return response()->json($registration);
    }

    /**
     * Update the specified registration.
     */
    public function update(Request $request, Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'job' => 'nullable|string|max:255',
            'health_status' => 'nullable|string|max:255',
            'insured' => 'nullable|boolean',
            'social_status' => 'nullable|in:divorced,widowed,low_income,cancer_patient',
            'nbr_in_family' => 'nullable|integer|min:1',
            'nbr_studing' => 'nullable|integer|min:0',
            'house_status' => 'nullable|in:owned,rented,host,other',
            'district_id' => 'nullable|exists:districts,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'city' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',

            // الزوجة
            'first_name_of_wife' => 'nullable|string|max:255',
            'last_name_of_wife' => 'nullable|string|max:255',
            'date_of_birth_of_wife' => 'nullable|date',
            'birth_place_of_wife' => 'nullable|string|max:255',
            'job_of_wife' => 'nullable|string|max:255',
            'health_status_of_wife' => 'nullable|string|max:255',
            'is_wife_insured' => 'nullable|boolean',

            'notes' => 'nullable|string',
        ]);

        $registration->update($validated);

        return response()->json([
            'message' => 'تم تحديث التسجيل بنجاح',
            'data' => $registration
        ]);
    }

    /**
     * Remove the specified registration.
     */
    public function destroy(Registration $registration)
    {
        if (!Auth::user() || !Auth::user()->can('التسجيلات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $registration->delete();

        return response()->json([
            'message' => 'تم حذف التسجيل بنجاح'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:اجصائيات المتطوعين')->only('statistics');
    //     $this->middleware('permission:عرض المتطوعين')->only('index');
    // }
    public function index()
    {
        return response()->json([
            'data' => Volunteer::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'membership_id' => 'required|string|unique:volunteers,membership_id',
            'gender' => 'required|in:male,female',
            'email' => 'nullable|email|max:255|unique:volunteers,email',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'subscriptions' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'skills' => 'nullable|string|max:255',
            'study_level' => 'nullable|in:primary,intermediate,secondary,high_school,bachelor,master,phd,other',
            'grade' => 'nullable|in:founder,active,honorary',
            'section' => 'nullable|in:planning,entry,executive,finance,management,resources,relations,media,social',
            'notes' => 'nullable|string',
        ]);

        $volunteer = Volunteer::create($validated);

        return response()->json([
            'message' => 'تم تسجيل المتطوع بنجاح',
            'data' => $volunteer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Volunteer $volunteer)
    {
        return response()->json([
            'data' => $volunteer
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Volunteer $volunteer)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'membership_id' => 'nullable|string|unique:volunteers,membership_id,' . $volunteer->id,
            'gender' => 'nullable|in:male,female',
            'email' => 'nullable|email|max:255|unique:volunteers,email,' . $volunteer->id,
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'subscriptions' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'skills' => 'nullable|string|max:255',
            'study_level' => 'nullable|in:primary,intermediate,secondary,high_school,bachelor,master,phd,other',
            'grade' => 'nullable|in:founder,active,honorary',
            'section' => 'nullable|in:planning,entry,executive,finance,management,resources,relations,media,social',
            'notes' => 'nullable|string',
        ]);

        $volunteer->update($validated);

        return response()->json([
            'message' => 'تم تحديث المتطوع بنجاح',
            'data' => $volunteer
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Volunteer $volunteer)
    {
        $volunteer->delete();

        return response()->json([
            'message' => 'تم حذف المتطوع بنجاح'
        ], 200);
    }

    /**
     * Statistics (API)
     */
    public function statistics()
    {
        return response()->json([
            'total' => Volunteer::count(),
            'male' => Volunteer::where('gender', 'male')->count(),
            'female' => Volunteer::where('gender', 'female')->count(),
        ], 200);
    }
}

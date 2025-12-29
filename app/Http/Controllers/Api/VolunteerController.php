<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VolunteerController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:اجصائيات المتطوعين')->only('statistics');
    //     $this->middleware('permission:عرض المتطوعين')->only('index');
    // }
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('عرض المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        if (!Auth::user()->can('عرض المتطوعين')) {
            abort(403, 'غير مصرح لك');
        }

        return response()->json([
            'data' => Volunteer::all()
        ], 200);
    }
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

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
    public function show(Volunteer $volunteer)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        return response()->json([
            'data' => $volunteer
        ], 200);
    }
    public function update(Request $request, Volunteer $volunteer)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

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
    public function destroy(Volunteer $volunteer)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $volunteer->delete();

        return response()->json([
            'message' => 'تم حذف المتطوع بنجاح'
        ], 200);
    }

    public function statistics()
    {
        if (!Auth::user() || !Auth::user()->can('اجصائيات المتطوعين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $now = Carbon::now();

        return response()->json([

            // 1️⃣ جميع المتطوعين
            'total_volunteers' => Volunteer::count(),

            // 2️⃣ المتطوعين المسجلين هذا الشهر (عدد)
            'registered_this_month' => Volunteer::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->count(),

            // 3️⃣ المتطوعين النشطين
            'active_volunteers' => Volunteer::where('is_active', true)->count(),

            // 4️⃣ المتطوعين المسجلين خلال آخر 6 شهور
            'registered_last_6_months' => Volunteer::where('created_at', '>=', $now->subMonths(6))
                ->count(),

            // 5️⃣ المتطوعين حسب الجنس
            'volunteers_by_gender' => Volunteer::select('gender', DB::raw('count(*) as total'))
                ->groupBy('gender')
                ->get(),

            // 6️⃣ المتطوعين حسب العمر
            'volunteers_by_age' => [
                'under_18' => Volunteer::whereDate('date_of_birth', '>', $now->subYears(18))->count(),
                '18_25' => Volunteer::whereBetween('date_of_birth', [
                    $now->subYears(25),
                    $now->subYears(18)
                ])->count(),
                '26_40' => Volunteer::whereBetween('date_of_birth', [
                    $now->subYears(40),
                    $now->subYears(26)
                ])->count(),
                'above_40' => Volunteer::whereDate('date_of_birth', '<', $now->subYears(40))->count(),
            ],

            // 7️⃣ المتطوعين حسب القسم
            'volunteers_by_section' => Volunteer::select('section', DB::raw('count(*) as total'))
                ->groupBy('section')
                ->get(),

        ], 200);
    }
}

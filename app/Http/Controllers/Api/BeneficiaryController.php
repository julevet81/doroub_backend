<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\District;
use App\Models\Municipality;
use App\Models\PartnerInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BeneficiaryController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        if (!Auth::user()->can('عرض المستفيدين')) {
            abort(403, 'غير مصرح لك');
        }

        $query = Beneficiary::query();

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

        $beneficiaries = $query->with(['partner', 'children'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $beneficiaries
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'partner' => 'nullable|array',
            'children' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $partnerId = null;
            if (!empty($validated['partner'])) {
                $partner = PartnerInfo::create($validated['partner']);
                $partnerId = $partner->id;
            }

            $nationalIdPath = null;
            if ($request->hasFile('national_id_file')) {
                $nationalIdPath = $request->file('national_id_file')->store('national_ids', 'public');
            }

            $beneficiary = Beneficiary::create(array_merge(
                $request->except(['partner', 'children', 'national_id_file']),
                [
                    'partner_id' => $partnerId,
                    'barcode' => uniqid(),
                    'national_id_file' => $nationalIdPath,
                ]
            ));

            if (!empty($request->children['kids'])) {
                foreach ($request->children['kids'] as $child) {
                    $beneficiary->children()->create($child);
                }
            }

            if (!empty($request->children['adults'])) {
                foreach ($request->children['adults'] as $child) {
                    $beneficiary->children()->create($child);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء المستفيد بنجاح',
                'data' => $beneficiary->load('partner', 'children')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء المستفيد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Beneficiary $beneficiary)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        return response()->json([
            'data' => $beneficiary->load('partner', 'children')
        ], 200);
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'social_status' => 'required|in:maried,single,divorced,widowed',
            'nbr_in_family' => 'required|integer|min:1',
            'partner_id' => 'nullable|exists:partners,id',
            'barcode' => 'required|string|max:100|unique:beneficiaries,barcode,' . $beneficiary->id,
            'district_id' => 'required|exists:districts,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'beneficiary_category_id' => 'nullable|exists:beneficiary_categories,id',
        ]);

        $beneficiary->update($validated);

        return response()->json([
            'message' => 'تم تحديث بيانات المستفيد بنجاح',
            'data' => $beneficiary->load('partner', 'children')
        ], 200);
    }

    public function destroy(Beneficiary $beneficiary)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $beneficiary->delete();

        return response()->json([
            'message' => 'تم حذف المستفيد بنجاح'
        ], 200);
    }

    public function getMunicipalities($district_id)
    {
        $municipalities = Municipality::where('district_id', $district_id)->get();

        return response()->json([
            'data' => $municipalities
        ], 200);
    }

    

    public function statistics()
    {
        if (!Auth::user() || !Auth::user()->can('إحصائيات المستفيدين')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $now = Carbon::now();

        // 1️⃣ العدد الإجمالي للمستفيدين
        $totalBeneficiaries = DB::table('beneficiaries')->count();

        // 2️⃣ عدد المستفيدين المسجلين هذا الشهر
        $thisMonthBeneficiaries = DB::table('beneficiaries')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // 3️⃣ متوسط عدد أفراد العائلات
        $averageFamilyMembers = DB::table('beneficiaries')
            ->whereNotNull('nbr_in_family')
            ->avg('nbr_in_family');

        // 4️⃣ عدد المسجلين خلال آخر 6 أشهر (حسب كل شهر)
        $lastSixMonths = DB::table('beneficiaries')
            ->selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as total
        ')
            ->where('created_at', '>=', $now->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 5️⃣ المستفيدين حسب الدائرة
        $beneficiariesByDistrict = DB::table('beneficiaries')
            ->join('districts', 'beneficiaries.district_id', '=', 'districts.id')
            ->select(
                'districts.id',
                'districts.name',
                DB::raw('COUNT(beneficiaries.id) as total')
            )
            ->groupBy('districts.id', 'districts.name')
            ->get();

        // 6️⃣ المستفيدين حسب الحالة الاجتماعية
        $beneficiariesBySocialStatus = DB::table('beneficiaries')
            ->select(
                'social_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('social_status')
            ->get();

        // 7️⃣ المستفيدين حسب حالة السكن
        $beneficiariesByHouseStatus = DB::table('beneficiaries')
            ->select(
                'house_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('house_status')
            ->get();

        return response()->json([
            'total_beneficiaries' => $totalBeneficiaries,
            'beneficiaries_this_month' => $thisMonthBeneficiaries,
            'average_family_members' => round($averageFamilyMembers, 2),
            'registrations_last_6_months' => $lastSixMonths,
            'by_district' => $beneficiariesByDistrict,
            'by_social_status' => $beneficiariesBySocialStatus,
            'by_house_status' => $beneficiariesByHouseStatus,
        ], 200);
    }
}

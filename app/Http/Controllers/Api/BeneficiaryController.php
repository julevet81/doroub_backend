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

class BeneficiaryController extends Controller
{
    public function index(Request $request)
    {
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
        return response()->json([
            'data' => $beneficiary->load('partner', 'children')
        ], 200);
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
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
            'beneficiary_category_id' => 'required|exists:beneficiary_categories,id',
        ]);

        $beneficiary->update($validated);

        return response()->json([
            'message' => 'تم تحديث بيانات المستفيد بنجاح',
            'data' => $beneficiary->load('partner', 'children')
        ], 200);
    }

    public function destroy(Beneficiary $beneficiary)
    {
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
        
        return response()->json([
            'message' => 'Endpoint for statistics',
            'data' => []
        ], 200);
    }
}

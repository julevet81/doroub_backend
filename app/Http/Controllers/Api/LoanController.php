<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\BeneficiaryCategory;
use App\Models\Device;
use App\Models\District;
use App\Models\Loan;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    // عرض جميع الإعارات
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('الإعارات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $loans = Loan::with(['device', 'beneficiary'])->get();
        return response()->json([
            'message' => 'تم جلب جميع الإعارات بنجاح',
            'data' => $loans
        ], 200);
    }
    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('الإعارات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'loan_date' => 'required|date',
            'new_beneficiary' => 'nullable|boolean',
        ]);

        if ($request->new_beneficiary == 1) {
            $beneficiary = Beneficiary::create([
                'beneficiary_category_id' => $request->beneficiary_category_id,
                'full_name' => $request->full_name,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'phone_1' => $request->phone_1,
                'social_status' => $request->social_status,
                'gender' => $request->gender,
                'municipality_id' => $request->municipality_id,
                'district_id' => $request->district_id,
                'barcode' => uniqid(),
                'nbr_in_family' => 1,
                'national_id' => uniqid(),
            ]);

            $beneficiary_id = $beneficiary->id;
        } else {
            $beneficiary_id = $request->beneficiary_id;
        }

        $loan = Loan::create([
            'device_id' => $request->device_id,
            'beneficiary_id' => $beneficiary_id,
            'new_beneficiary' => $request->new_beneficiary ? true : false,
            'loan_date' => $request->loan_date,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'تم تسجيل الإعارة بنجاح',
            'data' => $loan
        ], 201);
    }

    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('الإعارات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $loan = Loan::with(['device', 'beneficiary'])->findOrFail($id);

        return response()->json([
            'message' => 'تم جلب بيانات الإعارة بنجاح',
            'data' => $loan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('الإعارات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $loan = Loan::findOrFail($id);

        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'loan_date' => 'required|date',
        ]);

        // إذا كان المستفيد جديد
        if ($request->beneficiary_type == 'new') {
            if ($loan->new_beneficiary) {
                $beneficiary = Beneficiary::find($loan->beneficiary_id);
                $beneficiary->update([
                    'beneficiary_category_id' => $request->beneficiary_category_id,
                    'full_name' => $request->full_name,
                    'date_of_birth' => $request->date_of_birth,
                    'place_of_birth' => $request->place_of_birth,
                    'phone_1' => $request->phone_1,
                    'social_status' => $request->social_status,
                    'gender' => $request->gender,
                    'municipality_id' => $request->municipality_id,
                    'district_id' => $request->district_id,
                ]);
            } else {
                $newBeneficiary = Beneficiary::create([
                    'beneficiary_category_id' => $request->beneficiary_category_id,
                    'full_name' => $request->full_name,
                    'date_of_birth' => $request->date_of_birth,
                    'place_of_birth' => $request->place_of_birth,
                    'phone_1' => $request->phone_1,
                    'social_status' => $request->social_status,
                    'gender' => $request->gender,
                    'municipality_id' => $request->municipality_id,
                    'district_id' => $request->district_id,
                    'barcode' => uniqid(),
                    'nbr_in_family' => 1,
                    'national_id' => uniqid(),
                ]);
                $loan->beneficiary_id = $newBeneficiary->id;
                $loan->new_beneficiary = true;
            }
        } else {
            $loan->beneficiary_id = $request->beneficiary_id;
            $loan->new_beneficiary = false;
        }

        $loan->update([
            'device_id' => $request->device_id,
            'loan_date' => $request->loan_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'تم تعديل الإعارة بنجاح',
            'data' => $loan
        ], 200);
    }

    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->can('الإعارات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $loan = Loan::findOrFail($id);
        $loan->delete();

        return response()->json([
            'message' => 'تم حذف الإعارة بنجاح'
        ], 200);
    }
}

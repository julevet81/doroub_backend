<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::all();
        return response()->json([
            'message' => 'تم جلب جميع الدوائر بنجاح',
            'data' => $districts
        ], 200);
    }

    public function show(District $district)
    {
        return response()->json([
            'message' => 'تم جلب بيانات الدائرة بنجاح',
            'data' => $district
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $district = District::create($request->all());

        return response()->json([
            'message' => 'تم إضافة الدائرة بنجاح',
            'data' => $district
        ], 201);
    }

    public function update(Request $request, District $district)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $district->update($request->all());

        return response()->json([
            'message' => 'تم تحديث الدائرة بنجاح',
            'data' => $district
        ], 200);
    }

    public function destroy(District $district)
    {
        $district->delete();

        return response()->json([
            'message' => 'تم حذف الدائرة بنجاح'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    // عرض كل البلديات
    public function index()
    {
        $municipalities = Municipality::all();
        return response()->json([
            'message' => 'تم جلب جميع البلديات بنجاح',
            'data' => $municipalities
        ], 200);
    }

    // عرض بلدية واحدة
    public function show(Municipality $municipality)
    {
        return response()->json([
            'message' => 'تم جلب بيانات البلدية بنجاح',
            'data' => $municipality
        ], 200);
    }

    // إنشاء بلدية جديدة
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        $municipality = Municipality::create($request->all());

        return response()->json([
            'message' => 'تم إضافة البلدية بنجاح',
            'data' => $municipality
        ], 201);
    }

    // تحديث بيانات البلدية
    public function update(Request $request, Municipality $municipality)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        $municipality->update($request->all());

        return response()->json([
            'message' => 'تم تحديث البلدية بنجاح',
            'data' => $municipality
        ], 200);
    }

    // حذف البلدية
    public function destroy(Municipality $municipality)
    {
        $municipality->delete();

        return response()->json([
            'message' => 'تم حذف البلدية بنجاح'
        ], 200);
    }
}

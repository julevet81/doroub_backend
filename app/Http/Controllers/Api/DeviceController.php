<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return response()->json(['data' => $devices], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:devices',
            'is_new' => 'required|boolean',
        ]);

        $validated['barcode'] = mt_rand(100000000000, 999999999999);
        $device = Device::create($validated);

        return response()->json([
            'message' => 'تم إضافة الجهاز بنجاح',
            'data' => $device
        ], 201);
    }

    public function show($id)
    {
        $device = Device::findOrFail($id);
        return response()->json(['data' => $device], 200);
    }

    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:devices,serial_number,' . $device->id,
            'status' => 'required|boolean',
        ]);

        $device->update($validated);

        return response()->json([
            'message' => 'تم تحديث الجهاز بنجاح',
            'data' => $device
        ], 200);
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return response()->json(['message' => 'تم حذف الجهاز بنجاح'], 200);
    }

    // الأجهزة المعارة
    public function loaned()
    {
        $devices = Device::where('status', 1)->get();
        return response()->json(['data' => $devices], 200);
    }

    // الأجهزة المعادة
    public function returned()
    {
        $devices = Device::where('status', 0)->where('is_destructed', 0)->get();
        return response()->json(['data' => $devices], 200);
    }

    public function destruct(Request $request, Device $device)
    {
        $request->validate([
            'destruction_reason' => 'required|string|max:1000',
        ]);

        $device->update([
            'destruction_reason' => $request->destruction_reason,
            'is_destructed' => true,
        ]);

        return response()->json([
            'message' => 'تم تسجيل التدمير بنجاح',
            'device' => $device
        ], 200);
    }

    // الأجهزة المتلفة
    public function destructed()
    {
        $devices = Device::where('is_destructed', true)->get();
        return response()->json(['data' => $devices], 200);
    }


}

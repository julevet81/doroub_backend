<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Benefice;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeneficeController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $benefices = Benefice::with('beneficiary')->paginate(20);

        return response()->json([
            'data' => $benefices
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'type' => 'required|in:financial,material,service',
        ]);

        $benefice = Benefice::create($validated);

        return response()->json([
            'message' => 'تم إنشاء الإستفادة بنجاح.',
            'data' => $benefice
        ], 201);
    }

    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $benefice = Benefice::with('beneficiary')->findOrFail($id);

        return response()->json([
            'data' => $benefice
        ], 200);
    }

    /**
     * Update a specific benefice.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض الإستفادات')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $benefice = Benefice::findOrFail($id);

        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'type' => 'required|in:financial,material,service',
        ]);

        $benefice->update($validated);

        return response()->json([
            'message' => 'تم تحديث الإستفادة بنجاح.',
            'data' => $benefice
        ], 200);
    }

    /**
     * Delete a specific benefice.
     */
    public function destroy($id)
    {
        $benefice = Benefice::findOrFail($id);
        $benefice->delete();

        return response()->json([
            'message' => 'تم حذف الإستفادة بنجاح.'
        ], 200);
    }
}

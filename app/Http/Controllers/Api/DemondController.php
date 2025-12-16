<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demond;
use App\Models\AssistanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemondController extends Controller
{
    /**
     * Display a listing of demonds.
     */
    public function index()
    {
        $demonds = Demond::with(['beneficiary', 'assistanceItems'])->paginate(20);

        return response()->json([
            'data' => $demonds
        ], 200);
    }

    /**
     * Store a newly created demond.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'demand_date' => 'required|date',
            'attachement' => 'nullable|file|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('attachement')) {
            $validated['attachement'] = $request->file('attachement')->store('demonds', 'public');
        }

        $validated['treated_by'] = Auth::id(); // optional

        $demond = Demond::create($validated);

        return response()->json([
            'message' => 'تم إضافة الطلب بنجاح',
            'data' => $demond
        ], 201);
    }

    /**
     * Display the specified demond.
     */
    public function show($id)
    {
        $demond = Demond::with(['beneficiary', 'assistanceItems'])->findOrFail($id);

        return response()->json([
            'data' => $demond
        ], 200);
    }

    /**
     * Update the specified demond.
     */
    public function update(Request $request, $id)
    {
        $demond = Demond::findOrFail($id);

        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'demand_date' => 'required|date',
            'treated_by' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $demond->update($validated);

        return response()->json([
            'message' => 'تم تحديث الطلب بنجاح',
            'data' => $demond
        ], 200);
    }

    /**
     * Remove the specified demond.
     */
    public function destroy($id)
    {
        $demond = Demond::findOrFail($id);
        $demond->delete();

        return response()->json([
            'message' => 'تم حذف الطلب بنجاح'
        ], 200);
    }
}

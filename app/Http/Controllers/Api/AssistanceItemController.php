<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistanceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('عناصر المخزون')) {
            abort(403, 'غير مصرح لك');
        }
        return response()->json([
            'data' => AssistanceItem::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $barcode = random_int(1000000000, 9999999999);

        $item = AssistanceItem::create([
            'name' => $validated['name'],
            'code' => $barcode,
        ]);

        return response()->json([
            'message' => 'تم إنشاء عنصر المساعدة بنجاح',
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AssistanceItem $assistanceItem)
    {
        return response()->json([
            'data' => $assistanceItem
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssistanceItem $assistanceItem)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'quantity_in_stock' => 'sometimes|integer|min:0',
        ]);

        $assistanceItem->update($validated);

        return response()->json([
            'message' => 'تم تحديث عنصر المساعدة بنجاح',
            'data' => $assistanceItem
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssistanceItem $assistanceItem)
    {
        $assistanceItem->delete();

        return response()->json([
            'message' => 'تم حذف عنصر المساعدة بنجاح'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistanceItemController extends Controller
{
    
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('عناصر المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
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
        if (!Auth::user() || !Auth::user()->can('عناصر المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity_in_stock' => 'sometimes|integer|min:0',
        ]);

        $barcode = random_int(1000000000, 9999999999);

        $item = AssistanceItem::create([
            'name' => $validated['name'],
            'quantity_in_stock' => $validated['quantity_in_stock'] ?? 0,
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
        if (!Auth::user() || !Auth::user()->can('عناصر المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        return response()->json([
            'data' => $assistanceItem
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssistanceItem $assistanceItem)
    {
        if (!Auth::user() || !Auth::user()->can('عناصر المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
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
        if (!Auth::user() || !Auth::user()->can('عناصر المخزون')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $assistanceItem->delete();

        return response()->json([
            'message' => 'تم حذف عنصر المساعدة بنجاح'
        ], 200);
    }
}

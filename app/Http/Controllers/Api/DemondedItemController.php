<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemondedItem;
use Illuminate\Http\Request;

class DemondedItemController extends Controller
{
    /**
     * Display a listing of demonded items.
     */
    public function index()
    {
        $demondedItems = DemondedItem::with(['demond', 'assistanceItem'])->paginate(20);

        return response()->json([
            'data' => $demondedItems
        ], 200);
    }

    /**
     * Store a newly created demonded item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'demond_id' => 'required|exists:demonds,id',
            'assistance_item_id' => 'required|exists:assistance_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $demondedItem = DemondedItem::create($validated);

        return response()->json([
            'message' => 'تم إنشاء العنصر المطلوب بنجاح.',
            'data' => $demondedItem
        ], 201);
    }

    /**
     * Display the specified demonded item.
     */
    public function show($id)
    {
        $demondedItem = DemondedItem::with(['demond', 'assistanceItem'])->findOrFail($id);

        return response()->json([
            'data' => $demondedItem
        ], 200);
    }

    /**
     * Update the specified demonded item.
     */
    public function update(Request $request, $id)
    {
        $demondedItem = DemondedItem::findOrFail($id);

        $validated = $request->validate([
            'demond_id' => 'required|exists:demonds,id',
            'assistance_item_id' => 'required|exists:assistance_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $demondedItem->update($validated);

        return response()->json([
            'message' => 'تم تحديث العنصر المطلوب بنجاح.',
            'data' => $demondedItem
        ], 200);
    }

    /**
     * Remove the specified demonded item.
     */
    public function destroy($id)
    {
        $demondedItem = DemondedItem::findOrFail($id);
        $demondedItem->delete();

        return response()->json([
            'message' => 'تم حذف العنصر المطلوب بنجاح.'
        ], 200);
    }
}

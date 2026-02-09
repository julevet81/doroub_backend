<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Volunteer;
use App\Models\VolunteerSubscription;
use Illuminate\Http\Request;

class VolunteerSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => VolunteerSubscription::all()
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'volunteer_id' => 'required|exists:volunteers,id',
            'amount' => 'required|numeric',
            'subscription_date' => 'required|date',
        ]);

        $volunteer = Volunteer::find($validated['volunteer_id']);

        $subscription = VolunteerSubscription::create($validated);

        // تسجيل العملية المالية
        $lastBalance = FinancialTransaction::latest()->value('new_balance') ?? 0;

        FinancialTransaction::create([
            'amount' => $subscription->amount,
            'transaction_type' => 'income',
            'orientation' => 'treasury',
            'payment_method' => 'cash',
            'previous_balance' => $lastBalance,
            'new_balance' => $lastBalance + $subscription->amount,
            'description' => 'اشتراك متطوع: ' . $volunteer->full_name,
            'transaction_date' => now(),
        ]);

        return response()->json([
            'message' => 'تم إنشاء اشتراك المتطوع بنجاح.',
            'data' => $subscription
        ], 201);
    }

    public function show(string $id)
    {
        $subscription = VolunteerSubscription::findOrFail($id);

        return response()->json([
            'data' => $subscription
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $subscription = VolunteerSubscription::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'nullable|numeric',
            'subscription_date' => 'nullable|date',
        ]);

        $subscription->update($validated);

        return response()->json([
            'message' => 'تم تحديث اشتراك المتطوع بنجاح.',
            'data' => $subscription
        ], 200);
    }

    
    public function destroy(string $id)
    {
        $subscription = VolunteerSubscription::findOrFail($id);
        $subscription->delete();

        return response()->json([
            'message' => 'تم حذف اشتراك المتطوع بنجاح.'
        ], 200);
    }
}

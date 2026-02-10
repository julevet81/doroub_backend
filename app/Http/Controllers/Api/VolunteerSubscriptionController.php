<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Volunteer;
use App\Models\VolunteerSubscription;
use Illuminate\Http\Request;

class VolunteerSubscriptionController extends Controller
{
    // عرض جميع اشتراكات متطوع معيّن
    public function index($volunteerId)
    {
        $volunteer = Volunteer::with('subscriptions')->findOrFail($volunteerId);

        return response()->json([
            'volunteer' => $volunteer->only(['id', 'name']),
            'subscriptions' => $volunteer->subscriptions
        ]);
    }

    // إنشاء اشتراك جديد لمتطوع
    public function store(Request $request, $volunteerId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'subscription_date' => 'required|date',
        ]);

        $subscription = VolunteerSubscription::create([
            'volunteer_id' => $volunteerId,
            'amount' => $request->amount,
            'subscription_date' => $request->subscription_date,
        ]);

        return response()->json([
            'message' => 'تم إضافة الاشتراك بنجاح',
            'data' => $subscription
        ], 201);
    }

    // تعديل اشتراك
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'subscription_date' => 'nullable|date',
        ]);

        $subscription = VolunteerSubscription::findOrFail($id);
        $subscription->update($request->only([
            'amount',
            'subscription_date'
        ]));

        return response()->json([
            'message' => 'تم تعديل الاشتراك بنجاح',
            'data' => $subscription
        ]);
    }

    // حذف اشتراك
    public function destroy($id)
    {
        $subscription = VolunteerSubscription::findOrFail($id);
        $subscription->delete();

        return response()->json([
            'message' => 'تم حذف الاشتراك بنجاح'
        ]);
    }
}

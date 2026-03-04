<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceItem;
use App\Models\FinancialTransaction;
use App\Models\Project;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $projects = Project::with(['items', 'volunteers'])->get();

        return response()->json([
            'data' => $projects
        ], 200);
    }

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:relief,solidarity,healthyh,educational,entertainment,awareness,celebration',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:planned,in_progress,completed,rejected',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            // عناصر المشروع
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer|exists:assistance_items,id',
            'items.*.name' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',

            // متطوعو المشروع
            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'nullable|integer|exists:volunteers,id',
            'volunteers.*.full_name' => 'nullable|string|max:255',
            'volunteers.*.position' => 'nullable|string|in:coordinator,supervisor,volunteer,responsible,other',
        ]);

        // 🔴 التحقق من توفر الميزانية
        $availableBalance =
            FinancialTransaction::where('orientation', 'treasury')
            ->selectRaw("
            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0)
            as balance
        ")
            ->value('balance');

        if ($validated['budget'] > $availableBalance) {
            return response()->json([
                'message' => 'لا يوجد رصيد كافٍ في الخزينة لإنشاء هذا المشروع',
                'available_balance' => $availableBalance,
                'required_budget' => $validated['budget'],
            ], 422);
        }

        DB::beginTransaction();

        try {

            // ✅ إنشاء المشروع
            $project = Project::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'budget' => $validated['budget'],
                'remaining_amount' => $validated['budget'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // ✅ إضافة عناصر المشروع
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {

                    if (!empty($itemData['id'])) {
                        $item = AssistanceItem::find($itemData['id']);
                    } elseif (!empty($itemData['name'])) {

                        $barcode = random_int(1000000000, 9999999999);

                        $item = AssistanceItem::create([
                            'name' => $itemData['name'],
                            'quantity_in_stock' => 0,
                            'code' => $barcode,
                        ]);
                    } else {
                        throw new \Exception('يجب إدخال id أو name للعنصر');
                    }

                    $project->items()->attach($item->id, [
                        'quantity' => $itemData['quantity'],
                        'rest_in_project' => $itemData['quantity'],
                    ]);
                }
            }

            // ✅ إضافة المتطوعين (موجود أو جديد)
            if (!empty($validated['volunteers'])) {

                foreach ($validated['volunteers'] as $volunteerData) {

                    // إذا أرسل id
                    if (!empty($volunteerData['id'])) {

                        $volunteer = Volunteer::find($volunteerData['id']);
                    }
                    // إذا أرسل full_name فقط → إنشاء جديد
                    elseif (!empty($volunteerData['full_name'])) {

                        $membershipId = $volunteerData['membership_id']
                            ?? 'AUTO-' . strtoupper(uniqid());

                        $volunteer = Volunteer::create([
                            'full_name' => $volunteerData['full_name'],
                            'membership_id' => $membershipId,
                            'gender' => $volunteerData['gender'] ?? 'male',
                            'email' => $volunteerData['email'] ?? null,
                            'phone_1' => $volunteerData['phone_1'] ?? null,
                            'phone_2' => $volunteerData['phone_2'] ?? null,
                            'address' => $volunteerData['address'] ?? null,
                            'date_of_birth' => $volunteerData['date_of_birth'] ?? null,
                            'national_id' => $volunteerData['national_id'] ?? null,
                            'joining_date' => $volunteerData['joining_date'] ?? now(),
                            'skills' => $volunteerData['skills'] ?? null,
                            'study_level' => $volunteerData['study_level'] ?? null,
                            'grade' => $volunteerData['grade'] ?? 'active',
                            'section' => $volunteerData['section'] ?? null,
                            'notes' => $volunteerData['notes'] ?? null,
                        ]);
                    } else {

                        throw new \Exception('يجب إدخال id أو full_name للمتطوع');
                    }

                    $project->volunteers()->attach($volunteer->id, [
                        'position' => $volunteerData['position'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء المشروع بنجاح',
                'data' => $project->load(['items', 'volunteers'])
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'فشل إنشاء المشروع',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Project $project)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        return response()->json([
            'data' => $project->load(['items', 'volunteers'])
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string',
            'type' => 'nullable|string|in:relief,solidarity,healthyh,educational,entertainment,awareness,celebration',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planned,in_progress,completed,rejected',
            'location' => 'nullable|string',
            'description' => 'nullable|string',

            // عناصر المشروع
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer|exists:assistance_items,id',
            'items.*.name' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',

            // المتطوعون (موجود أو جديد)
            'volunteers' => 'nullable|array',
            'volunteers.*.id' => 'nullable|integer|exists:volunteers,id',
            'volunteers.*.full_name' => 'nullable|string|max:255',
            'volunteers.*.position' => 'nullable|string|in:coordinator,supervisor,volunteer,responsible,other',
        ]);

        $project = Project::findOrFail($id);

        /*
    |--------------------------------------------------------------------------
    | التحقق من توفر الرصيد عند زيادة الميزانية
    |--------------------------------------------------------------------------
    */
        if (isset($validated['budget'])) {
            $difference = $validated['budget'] - $project->budget;

            if ($difference > 0) {
                $availableBalance =
                    FinancialTransaction::where('orientation', 'treasury')
                    ->selectRaw("
                    COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0)
                    as balance
                ")
                    ->value('balance');

                if ($difference > $availableBalance) {
                    return response()->json([
                        'message' => 'لا يوجد رصيد كافٍ في الخزينة لزيادة ميزانية المشروع',
                        'available_balance' => $availableBalance,
                        'required_increase' => $difference,
                    ], 422);
                }
            }
        }

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | تحديث الميزانية مع الحفاظ على remaining_amount
        |--------------------------------------------------------------------------
        */
            if (isset($validated['budget'])) {
                $difference = $validated['budget'] - $project->budget;

                if ($difference > 0) {
                    $project->remaining_amount += $difference;
                } else {
                    if ($project->remaining_amount < abs($difference)) {
                        throw new \Exception('لا يمكن تخفيض الميزانية لأن remaining_amount لا يسمح بذلك');
                    }
                    $project->remaining_amount += $difference;
                }
            }

            $project->update(
                collect($validated)->except(['items', 'volunteers'])->toArray()
            );

            /*
        |--------------------------------------------------------------------------
        | تحديث عناصر المشروع
        |--------------------------------------------------------------------------
        */
            if (!empty($validated['items'])) {

                foreach ($validated['items'] as $itemData) {

                    if (!empty($itemData['id'])) {
                        $item = AssistanceItem::find($itemData['id']);
                    } elseif (!empty($itemData['name'])) {

                        $barcode = random_int(1000000000, 9999999999);

                        $item = AssistanceItem::create([
                            'name' => $itemData['name'],
                            'quantity_in_stock' => 0,
                            'code' => $barcode,
                        ]);
                    } else {
                        throw new \Exception('يجب إدخال id أو name للعنصر');
                    }

                    $existing = $project->items()
                        ->where('assistance_item_id', $item->id)
                        ->first();

                    if ($existing) {

                        if ($existing->pivot->rest_in_project > $itemData['quantity']) {
                            throw new \Exception('لا يمكن وضع كمية أقل من rest_in_project الحالية');
                        }

                        $project->items()->updateExistingPivot($item->id, [
                            'quantity' => $itemData['quantity'],
                        ]);
                    } else {

                        $project->items()->attach($item->id, [
                            'quantity' => $itemData['quantity'],
                            'rest_in_project' => $itemData['quantity'],
                        ]);
                    }
                }
            }

            /*
        |--------------------------------------------------------------------------
        | تحديث المتطوعين (موجود أو إنشاء جديد)
        |--------------------------------------------------------------------------
        */
            if (!empty($validated['volunteers'])) {

                $volunteersData = [];

                foreach ($validated['volunteers'] as $volunteerData) {

                    if (!empty($volunteerData['id'])) {

                        $volunteer = Volunteer::find($volunteerData['id']);
                    } elseif (!empty($volunteerData['full_name'])) {

                        $membershipId = $volunteerData['membership_id']
                            ?? 'AUTO-' . strtoupper(uniqid());

                        $volunteer = Volunteer::create([
                            'full_name' => $volunteerData['full_name'],
                            'membership_id' => $membershipId,
                            'gender' => $volunteerData['gender'] ?? 'male',
                            'email' => $volunteerData['email'] ?? null,
                            'phone_1' => $volunteerData['phone_1'] ?? null,
                            'phone_2' => $volunteerData['phone_2'] ?? null,
                            'address' => $volunteerData['address'] ?? null,
                            'date_of_birth' => $volunteerData['date_of_birth'] ?? null,
                            'national_id' => $volunteerData['national_id'] ?? null,
                            'joining_date' => $volunteerData['joining_date'] ?? now(),
                            'skills' => $volunteerData['skills'] ?? null,
                            'study_level' => $volunteerData['study_level'] ?? null,
                            'grade' => $volunteerData['grade'] ?? 'active',
                            'section' => $volunteerData['section'] ?? null,
                            'notes' => $volunteerData['notes'] ?? null,
                        ]);
                    } else {
                        throw new \Exception('يجب إدخال id أو full_name للمتطوع');
                    }

                    $volunteersData[$volunteer->id] = [
                        'position' => $volunteerData['position'] ?? null,
                    ];
                }

                $project->volunteers()->sync($volunteersData);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تعديل المشروع بنجاح',
                'data' => $project->load(['items', 'volunteers']),
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'فشل تعديل المشروع',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Project $project)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المشاريع')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $project->delete();

        return response()->json([
            'message' => 'تم حذف المشروع بنجاح'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\FinancialTransaction;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المصاريف')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        // =======================
        // Dates
        // =======================
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth   = Carbon::now()->endOfMonth();

        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth   = Carbon::now()->subMonth()->endOfMonth();

        // =======================
        // Base Query (Reusable)
        // =======================
        $baseExpenseQuery = FinancialTransaction::query()
            ->where('transaction_type', 'expense');

        // =======================
        // All Expenses (Paginated)
        // =======================
        $expenses = (clone $baseExpenseQuery)
            ->with(['donor', 'project', 'beneficiary'])
            ->when(
                $request->start_date && $request->end_date,
                fn($q) => $q->whereBetween('transaction_date', [
                    $request->start_date,
                    $request->end_date
                ])
            )
            ->orderByDesc('transaction_date')
            ->paginate(20);

        // =======================
        // This Month Expenses
        // =======================
        $thisMonthExpenses = (clone $baseExpenseQuery)
            ->whereBetween('transaction_date', [$startOfThisMonth, $endOfThisMonth]);

        $thisMonthTotal = (clone $thisMonthExpenses)->sum('amount');
        $thisMonthCount = (clone $thisMonthExpenses)->count();

        // =======================
        // Last Month Expenses
        // =======================
        $lastMonthExpenses = (clone $baseExpenseQuery)
            ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth]);

        $lastMonthTotal = (clone $lastMonthExpenses)->sum('amount');
        $lastMonthCount = (clone $lastMonthExpenses)->count();

        // =======================
        // Project Expenses Comparison
        // =======================
        $thisMonthProjectExpenses = (clone $thisMonthExpenses)
            ->where('out_orientation', 'project')
            ->sum('amount');

        $lastMonthProjectExpenses = (clone $lastMonthExpenses)
            ->where('out_orientation', 'project')
            ->sum('amount');

        // =======================
        // Balance
        // =======================
        $currentBalance = FinancialTransaction::latest('transaction_date')
            ->value('new_balance');

        $lastMonthBalance = FinancialTransaction::whereDate(
            'transaction_date',
            '<=',
            $endOfLastMonth
        )
            ->latest('transaction_date')
            ->value('new_balance');

        // =======================
        // Response
        // =======================
        return response()->json([
            'data' => [
                'expenses' => $expenses,

                'statistics' => [
                    'this_month' => [
                        'total_expenses' => $thisMonthTotal,
                        'count' => $thisMonthCount,
                        'project_expenses' => $thisMonthProjectExpenses,
                    ],
                    'last_month' => [
                        'total_expenses' => $lastMonthTotal,
                        'count' => $lastMonthCount,
                        'project_expenses' => $lastMonthProjectExpenses,
                    ],
                    'comparison' => [
                        'expenses_difference' => $thisMonthTotal - $lastMonthTotal,
                        'count_difference' => $thisMonthCount - $lastMonthCount,
                        'project_expenses_difference' => $thisMonthProjectExpenses - $lastMonthProjectExpenses,
                        'balance_difference' => ($currentBalance ?? 0) - ($lastMonthBalance ?? 0),
                    ],
                    'balance' => [
                        'current' => $currentBalance,
                        'last_month' => $lastMonthBalance,
                    ],
                ]
            ]
        ], 200);
    }

    

    public function store(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المصاريف')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'out_orientation' => 'required|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'project_id' => 'required_if:out_orientation,project|nullable|exists:projects,id',
            'beneficiary_id' => 'required_if:out_orientation,sponsored_family|nullable|exists:beneficiaries,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $request) {

            // 🔹 جلب رصيد الخزينة الحالي
            $currentBalance = FinancialTransaction::latest()->value('new_balance') ?? 0;

            // 🔹 التحقق من كفاية الرصيد
            if ($validated['amount'] > $currentBalance) {
                return response()->json([
                    'message' => 'رصيد الخزينة غير كافٍ لإتمام العملية',
                    'current_balance' => $currentBalance
                ], 422);
            }

            // 🔹 حساب الرصيد الجديد
            $newBalance = $currentBalance - $validated['amount'];

            $data = [
                'out_orientation'   => $validated['out_orientation'],
                'transaction_type'  => 'expense',
                'transaction_date'  => $validated['transaction_date'],
                'amount'            => $validated['amount'],
                'previous_balance'  => $currentBalance,
                'new_balance'       => $newBalance,
                'description'       => $validated['notes'] ?? null,
            ];

            if ($validated['out_orientation'] === 'project') {
                $data['project_id'] = $validated['project_id'];
            } elseif ($validated['out_orientation'] === 'sponsored_family') {
                $data['beneficiary_id'] = $validated['beneficiary_id'];
            }

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
            }

            $expense = FinancialTransaction::create($data);

            return response()->json([
                'message' => 'تم حفظ المصروف بنجاح',
                'data' => $expense,
                'remaining_balance' => $newBalance
            ], 201);
        });
    }


    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المصاريف')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $expense = FinancialTransaction::with(['donor', 'project', 'beneficiary'])->findOrFail($id);

        return response()->json([
            'data' => $expense
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المصاريف')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $expense = FinancialTransaction::findOrFail($id);

        // تأكد أنه مصروف
        if ($expense->transaction_type !== 'expense') {
            return response()->json([
                'message' => 'لا يمكن تعديل عملية ليست مصروفًا'
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'out_orientation' => 'nullable|in:project,sponsored_family,services,electricity,maintenance,internet,cleaning,generals',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'beneficiary_id' => 'nullable|exists:beneficiaries,id',
        ]);

        return DB::transaction(function () use ($expense, $validated) {

            // 🔹 الرصيد الحالي للخزينة
            $currentBalance = FinancialTransaction::latest()->value('new_balance') ?? 0;

            // 🔹 إعادة المبلغ القديم مؤقتًا
            $restoredBalance = $currentBalance + $expense->amount;

            // 🔹 التحقق من كفاية الرصيد للمبلغ الجديد
            if ($validated['amount'] > $restoredBalance) {
                return response()->json([
                    'message' => 'رصيد الخزينة غير كافٍ بعد التعديل',
                    'available_balance' => $restoredBalance
                ], 422);
            }

            // 🔹 حساب الرصيد الجديد
            $newBalance = $restoredBalance - $validated['amount'];

            // 🔹 تحديث المصروف
            $expense->update([
                'amount'            => $validated['amount'],
                'out_orientation'   => $validated['out_orientation'] ?? $expense->out_orientation,
                'transaction_date'  => $validated['transaction_date'],
                'description'       => $validated['notes'] ?? null,
                'project_id'        => $validated['project_id'] ?? null,
                'beneficiary_id'    => $validated['beneficiary_id'] ?? null,
                'previous_balance'  => $restoredBalance,
                'new_balance'       => $newBalance,
            ]);

            return response()->json([
                'message' => 'تم تحديث المصروف بنجاح',
                'data' => $expense,
                'remaining_balance' => $newBalance
            ], 200);
        });
    }


    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المصاريف')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }
        $expense = FinancialTransaction::findOrFail($id);

        if ($expense->attachment) {
            Storage::disk('public')->delete($expense->attachment);
        }

        $expense->delete();

        return response()->json([
            'message' => 'تم حذف المصروف بنجاح.'
        ], 200);
    }
}

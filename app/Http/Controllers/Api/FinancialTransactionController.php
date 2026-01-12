<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Donor;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinancialTransactionController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('المداخيل')) {
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
        // Base Income Query
        // =======================
        $baseIncomeQuery = FinancialTransaction::query()
            ->where('transaction_type', 'income');

        // =======================
        // All Incomes (Paginated)
        // =======================
        $incomes = (clone $baseIncomeQuery)
            ->with(['donor', 'project'])
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
        // This Month Incomes
        // =======================
        $thisMonthIncomes = (clone $baseIncomeQuery)
            ->whereBetween('transaction_date', [$startOfThisMonth, $endOfThisMonth]);

        $thisMonthTotal = (clone $thisMonthIncomes)->sum('amount');
        $thisMonthCount = (clone $thisMonthIncomes)->count();

        // =======================
        // Last Month Incomes
        // =======================
        $lastMonthIncomes = (clone $baseIncomeQuery)
            ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth]);

        $lastMonthTotal = (clone $lastMonthIncomes)->sum('amount');
        $lastMonthCount = (clone $lastMonthIncomes)->count();

        // =======================
        // Project Incomes Comparison
        // =======================
        $thisMonthProjectIncomes = (clone $thisMonthIncomes)
            ->where('orientation', 'project')
            ->sum('amount');

        $lastMonthProjectIncomes = (clone $lastMonthIncomes)
            ->where('orientation', 'project')
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
                'incomes' => $incomes,

                'statistics' => [
                    'this_month' => [
                        'total_incomes' => $thisMonthTotal,
                        'count' => $thisMonthCount,
                        'project_incomes' => $thisMonthProjectIncomes,
                    ],
                    'last_month' => [
                        'total_incomes' => $lastMonthTotal,
                        'count' => $lastMonthCount,
                        'project_incomes' => $lastMonthProjectIncomes,
                    ],
                    'comparison' => [
                        'total_difference' => $thisMonthTotal - $lastMonthTotal,
                        'count_difference' => $thisMonthCount - $lastMonthCount,
                        'project_difference' => $thisMonthProjectIncomes - $lastMonthProjectIncomes,
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
        if (!Auth::user() || !Auth::user()->can('المداخيل')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'orientation' => 'required|string|in:project,treasury,other', // نوع الإيراد
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'donor_id' => 'nullable|exists:donors,id',
            'project_id' => 'required_if:orientation,project|nullable|exists:projects,id',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        // =======================
        // حساب الرصيد الجديد
        // =======================
        $lastBalance = FinancialTransaction::latest('transaction_date')->value('new_balance') ?? 0;
        $newBalance = $lastBalance + $validated['amount'];

        // =======================
        // التحضير للإنشاء
        // =======================
        $data = [
            'transaction_type' => 'income',
            'orientation' => $validated['orientation'],
            'transaction_date' => $validated['transaction_date'],
            'amount' => $validated['amount'],
            'donor_id' => $validated['donor_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'previous_balance' => $lastBalance,
            'new_balance' => $newBalance,
        ];

        // =======================
        // رفع المرفق (إذا موجود)
        // =======================
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        // =======================
        // حفظ الإيراد داخل Transaction DB
        // =======================
        DB::beginTransaction();
        try {
            $income = FinancialTransaction::create($data);
            DB::commit();

            return response()->json([
                'message' => 'تم حفظ الإيراد بنجاح',
                'data' => $income
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء حفظ الإيراد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!Auth::user() || !Auth::user()->can('المداخيل')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        // =======================
        // جلب المعاملة مع العلاقات
        // =======================
        $transaction = FinancialTransaction::with([
            'donor:id,full_name,phone',          // جلب فقط الحقول الضرورية
            'project:id,name',              // جلب فقط الحقول الضرورية
            'beneficiary:id,full_name'     // جلب فقط الحقول الضرورية
        ])->find($id);

        // =======================
        // تحقق إذا المعاملة موجودة
        // =======================
        if (!$transaction) {
            return response()->json([
                'message' => 'المعاملة غير موجودة'
            ], 404);
        }

        // =======================
        // الإرجاع
        // =======================
        return response()->json([
            'data' => $transaction
        ], 200);
    }
    

    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->can('المداخيل')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $transaction = FinancialTransaction::find($id);

        if (!$transaction) {
            return response()->json([
                'message' => 'المعاملة غير موجودة'
            ], 404);
        }

        $validated = $request->validate([
            'orientation' => 'required|string|in:project,family,other',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'donor_id' => 'nullable|exists:donors,id',
            'project_id' => 'required_if:orientation,project|nullable|exists:projects,id',
            'beneficiary_id' => 'required_if:orientation,family|nullable|exists:beneficiaries,id',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        DB::beginTransaction();
        try {
            // =======================
            // تحديث المرفق إذا تم تغييره
            // =======================
            if ($request->hasFile('attachment')) {
                // حذف الملف القديم إذا موجود
                if ($transaction->attachment) {
                    Storage::disk('public')->delete($transaction->attachment);
                }
                $validated['attachment'] = $request->file('attachment')->store('attachments', 'public');
            }

            // =======================
            // تحديث الحقول
            // =======================
            $transaction->update([
                'orientation' => $validated['orientation'],
                'transaction_date' => $validated['transaction_date'],
                'amount' => $validated['amount'],
                'donor_id' => $validated['donor_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'beneficiary_id' => $validated['beneficiary_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'attachment' => $validated['attachment'] ?? $transaction->attachment,
            ]);

            // =======================
            // إعادة حساب الرصيد (optional)
            // =======================
            $lastTransaction = FinancialTransaction::where('id', '<=', $transaction->id)
                ->orderByDesc('transaction_date')
                ->first();

            $transaction->previous_balance = $lastTransaction ? $lastTransaction->previous_balance : 0;
            $transaction->new_balance = $transaction->previous_balance + $transaction->amount;
            $transaction->save();

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث المعاملة بنجاح',
                'data' => $transaction
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث المعاملة',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(FinancialTransaction $financialTransaction)
    {
        if (!Auth::user() || !Auth::user()->can('المداخيل')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $financialTransaction->delete();

        return response()->json([
            'message' => 'تم حذف المعاملة المالية بنجاح.'
        ], 200);
    }

    public function statistics(Request $request)
    {
        if (!Auth::user() || !Auth::user()->can('عرض المالية')) {
            return response()->json(['message' => 'غير مسموح لك بهذا الاجراء'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate   = Carbon::parse($validated['end_date'])->endOfDay();

        // =======================
        // قاعدة الاستعلام العامة للفترة
        // =======================
        $query = FinancialTransaction::whereBetween('transaction_date', [$startDate, $endDate]);

        // =======================
        // الإيرادات في الفترة
        // =======================
        $incomes = (clone $query)
            ->where('transaction_type', 'income')
            ->with(['donor', 'project'])
            ->get();

        $totalIncomes = $incomes->sum('amount');

        // =======================
        // النفقات في الفترة
        // =======================
        $expenses = (clone $query)
            ->where('transaction_type', 'expense')
            ->with(['donor', 'project', 'beneficiary'])
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // =======================
        // تحويلات المشاريع (Expenses + orientation = project)
        // =======================
        $projectTransfers = (clone $query)
            ->where('transaction_type', 'expense')
            ->where('out_orientation', 'project')
            ->with('project')
            ->get();

        $totalProjectTransfers = $projectTransfers->sum('amount');

        // =======================
        // الرصيد الحالي
        // =======================
        $currentBalance = FinancialTransaction::latest('transaction_date')
            ->value('new_balance');

        // =======================
        // إعداد الاستجابة
        // =======================
        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString()
            ],
            'summary' => [
                'incomes' => [
                    'total' => $totalIncomes,
                    'details' => $incomes
                ],
                'expenses' => [
                    'total' => $totalExpenses,
                    'details' => $expenses
                ],
                'project_transfers' => [
                    'total' => $totalProjectTransfers,
                    'details' => $projectTransfers
                ],
                'current_balance' => $currentBalance
            ]
        ], 200);
    }
}

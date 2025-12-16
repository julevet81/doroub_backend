<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Donor;
use App\Models\Project;
use Illuminate\Http\Request;

class FinancialTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = FinancialTransaction::with(['donor', 'project'])
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            })
            ->paginate(20);

        return response()->json([
            'data' => $transactions
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'transaction_date' => 'required|date',
            'orientation' => 'required|in:project,other',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'project_id' => 'required_if:orientation,project|exists:projects,id',
        ]);

        $transaction = FinancialTransaction::create([
            'donor_id' => $validated['donor_id'],
            'transaction_type' => 'income',
            'transaction_date' => $validated['transaction_date'],
            'orientation' => $validated['orientation'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'project_id' => $validated['orientation'] === 'project' ? $validated['project_id'] : null,
        ]);

        return response()->json([
            'message' => 'تم حفظ البيانات بنجاح.',
            'data' => $transaction
        ], 201);
    }

    public function show(FinancialTransaction $financialTransaction)
    {
        $financialTransaction->load(['donor', 'project']);

        return response()->json([
            'data' => $financialTransaction
        ], 200);
    }
    public function update(Request $request, FinancialTransaction $financialTransaction)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'transaction_type' => 'required|string|max:255',
            'donor_id' => 'nullable|exists:donors,id',
            'orientation' => 'nullable|in:project,family,other',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,other',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $financialTransaction->update($validated);

        return response()->json([
            'message' => 'تم تحديث المعاملة المالية بنجاح.',
            'data' => $financialTransaction
        ], 200);
    }

    public function destroy(FinancialTransaction $financialTransaction)
    {
        $financialTransaction->delete();

        return response()->json([
            'message' => 'تم حذف المعاملة المالية بنجاح.'
        ], 200);
    }

    public function statistics(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $transactions = FinancialTransaction::with('project')
            ->when($start && $end, fn($q) => $q->whereBetween('transaction_date', [$start, $end]))
            ->get();

        $totalIncome = $transactions->where('transaction_type', 'income')->sum('amount');
        $totalExpense = $transactions->where('transaction_type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $projectTransfers = $transactions
            ->where('orientation', 'project')
            ->groupBy('project_id')
            ->map(fn($items) => $items->sum('amount'));

        return response()->json([
            'transactions' => $transactions,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'project_transfers' => $projectTransfers
        ], 200);
    }
}

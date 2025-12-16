<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\FinancialTransaction;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = FinancialTransaction::where('transaction_type', 'expense')
            ->with(['donor', 'project', 'beneficiary'])
            ->when($request->start_date && $request->end_date, fn($q) => $q->whereBetween('transaction_date', [$request->start_date, $request->end_date]))
            ->paginate(20);

        return response()->json([
            'data' => $expenses
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'out_orientation' => 'required|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric',
            'project_id' => 'required_if:out_orientation,project|nullable|exists:projects,id',
            'beneficiary_id' => 'required_if:out_orientation,sponsored_family|nullable|exists:beneficiaries,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            'out_orientation' => $validated['out_orientation'],
            'transaction_type' => 'expense',
            'transaction_date' => $validated['transaction_date'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
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
            'message' => 'تم حفظ البيانات بنجاح',
            'data' => $expense
        ], 201);
    }

    public function show($id)
    {
        $expense = FinancialTransaction::with(['donor', 'project', 'beneficiary'])->findOrFail($id);

        return response()->json([
            'data' => $expense
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $expense = FinancialTransaction::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric',
            'out_orientation' => 'nullable|in:project,sponsored_family,other',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'beneficiary_id' => 'nullable|exists:beneficiaries,id',
        ]);

        $expense->update($validated);

        return response()->json([
            'message' => 'تم تحديث المصروف بنجاح.',
            'data' => $expense
        ], 200);
    }

    public function destroy($id)
    {
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

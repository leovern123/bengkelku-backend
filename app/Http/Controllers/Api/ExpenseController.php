<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil diambil',
            'data' => $expenses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|string|max:20|unique:expenses,expense_id',
            'user_id' => 'required|string|exists:users,user_id',
            'expense_name' => 'required|string|max:100',
            'expense_category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'expense_id' => $request->expense_id,
            'user_id' => $request->user_id,
            'expense_name' => $request->expense_name,
            'expense_category' => $request->expense_category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $expense
        ], 201);
    }

    public function show($id)
    {
        $expense = Expense::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pengeluaran berhasil diambil',
            'data' => $expense
        ]);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'user_id' => 'sometimes|required|string|exists:users,user_id',
            'expense_name' => 'sometimes|required|string|max:100',
            'expense_category' => 'nullable|string|max:100',
            'amount' => 'sometimes|required|numeric|min:0',
            'expense_date' => 'sometimes|required|date',
            'note' => 'nullable|string',
        ]);

        $expense->update([
            'user_id' => $request->user_id ?? $expense->user_id,
            'expense_name' => $request->expense_name ?? $expense->expense_name,
            'expense_category' => $request->expense_category ?? $expense->expense_category,
            'amount' => $request->amount ?? $expense->amount,
            'expense_date' => $request->expense_date ?? $expense->expense_date,
            'note' => $request->note ?? $expense->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diperbarui',
            'data' => $expense
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus'
        ]);
    }
}
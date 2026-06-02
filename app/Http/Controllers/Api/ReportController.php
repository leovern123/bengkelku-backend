<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Item;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary()
    {
        $totalIncome = Payment::where('payment_status', 'paid')->sum('paid_amount');
        $totalExpenses = Expense::sum('amount');
        $totalProfit = $totalIncome - $totalExpenses;

        $totalOrders = Order::count();
        $completedOrders = Order::where('order_status', 'completed')->count();
        $processOrders = Order::where('order_status', 'process')->count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $cancelledOrders = Order::where('order_status', 'cancelled')->count();

        $lowStockItems = Item::whereNotNull('stock')
            ->where('stock', '<=', 5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan laporan berhasil diambil',
            'data' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_profit' => $totalProfit,
                'orders' => [
                    'total' => $totalOrders,
                    'completed' => $completedOrders,
                    'process' => $processOrders,
                    'pending' => $pendingOrders,
                    'cancelled' => $cancelledOrders,
                ],
                'low_stock_items' => $lowStockItems,
            ]
        ]);
    }

    public function income(Request $request)
    {
        $query = Payment::with('order.customer')
            ->where('payment_status', 'paid');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('payment_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $payments = $query->latest()->get();
        $total = $payments->sum('paid_amount');

        return response()->json([
            'success' => true,
            'message' => 'Laporan pendapatan berhasil diambil',
            'data' => [
                'total_income' => $total,
                'payments' => $payments,
            ]
        ]);
    }

    public function expenses(Request $request)
    {
        $query = Expense::with('user');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        $expenses = $query->latest()->get();
        $total = $expenses->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Laporan pengeluaran berhasil diambil',
            'data' => [
                'total_expenses' => $total,
                'expenses' => $expenses,
            ]
        ]);
    }

    public function profit(Request $request)
    {
        $incomeQuery = Payment::where('payment_status', 'paid');
        $expenseQuery = Expense::query();

        if ($request->start_date && $request->end_date) {
            $incomeQuery->whereBetween('payment_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);

            $expenseQuery->whereBetween('expense_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        $totalIncome = $incomeQuery->sum('paid_amount');
        $totalExpenses = $expenseQuery->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuntungan berhasil diambil',
            'data' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_profit' => $totalIncome - $totalExpenses,
            ]
        ]);
    }

    public function stock()
    {
        $items = Item::with('category.itemType')
            ->whereNotNull('stock')
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan stok berhasil diambil',
            'data' => $items
        ]);
    }
}
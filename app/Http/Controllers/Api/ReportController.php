<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        $paidOrderIds = Payment::where('payment_status', 'paid')
            ->pluck('order_id');

        $totalIncome = Order::whereIn('order_id', $paidOrderIds)->sum('total_amount');

        $totalModal = OrderDetail::whereIn('order_id', $paidOrderIds)
            ->sum(DB::raw('quantity * purchase_price_at_transaction'));

        $totalExpenses = Expense::sum('amount');

        $labaKotor = $totalIncome - $totalModal;
        $labaBersih = $labaKotor - $totalExpenses;

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
                'total_income'   => (float) $totalIncome,
                'total_modal'    => (float) $totalModal,
                'total_expenses' => (float) $totalExpenses,
                'laba_kotor'     => (float) $labaKotor,
                'laba_bersih'    => (float) $labaBersih,
                'orders' => [
                    'total'     => $totalOrders,
                    'completed' => $completedOrders,
                    'process'   => $processOrders,
                    'pending'   => $pendingOrders,
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
        $total = $payments->sum(fn($p) => $p->order->total_amount ?? 0);

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

        $paidOrderIds = (clone $incomeQuery)->pluck('order_id');

        $totalIncome = Order::whereIn('order_id', $paidOrderIds)->sum('total_amount');

        $totalModal = OrderDetail::whereIn('order_id', $paidOrderIds)
            ->sum(DB::raw('quantity * purchase_price_at_transaction'));

        $totalExpenses = $expenseQuery->sum('amount');

        $labaKotor = $totalIncome - $totalModal;
        $labaBersih = $labaKotor - $totalExpenses;

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuntungan berhasil diambil',
            'data' => [
                'total_income'   => (float) $totalIncome,
                'total_modal'    => (float) $totalModal,
                'total_expenses' => (float) $totalExpenses,
                'laba_kotor'     => (float) $labaKotor,
                'laba_bersih'    => (float) $labaBersih,
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $query = Order::with(['customer', 'vehicle', 'payment', 'user'])
            ->withCount('details as item_count');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->get(),
        ]);
    }

    public function chart(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year   = (int) $request->get('year', now()->year);
        $month  = (int) $request->get('month', now()->month);

        $data = match ($period) {
            'daily'  => $this->chartDaily($year, $month),
            'yearly' => $this->chartYearly(),
            default  => $this->chartMonthly($year),
        };

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function chartDaily(int $year, int $month): array
    {
        $income = DB::table('payments as p')
            ->join('orders as o', 'o.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->whereYear('p.payment_date', $year)
            ->whereMonth('p.payment_date', $month)
            ->selectRaw('DAY(p.payment_date) as k, SUM(o.total_amount) as v')
            ->groupByRaw('DAY(p.payment_date)')
            ->pluck('v', 'k');

        $modal = DB::table('payments as p')
            ->join('order_details as od', 'od.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->whereYear('p.payment_date', $year)
            ->whereMonth('p.payment_date', $month)
            ->selectRaw('DAY(p.payment_date) as k, SUM(od.quantity * od.purchase_price_at_transaction) as v')
            ->groupByRaw('DAY(p.payment_date)')
            ->pluck('v', 'k');

        $expenses = DB::table('expenses')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->selectRaw('DAY(expense_date) as k, SUM(amount) as v')
            ->groupByRaw('DAY(expense_date)')
            ->pluck('v', 'k');

        $days = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $result = [];
        for ($d = 1; $d <= $days; $d++) {
            $result[] = [
                'label'    => (string) $d,
                'income'   => (float) ($income[$d] ?? 0),
                'modal'    => (float) ($modal[$d] ?? 0),
                'expenses' => (float) ($expenses[$d] ?? 0),
            ];
        }
        return $result;
    }

    private function chartMonthly(int $year): array
    {
        $labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        $income = DB::table('payments as p')
            ->join('orders as o', 'o.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->whereYear('p.payment_date', $year)
            ->selectRaw('MONTH(p.payment_date) as k, SUM(o.total_amount) as v')
            ->groupByRaw('MONTH(p.payment_date)')
            ->pluck('v', 'k');

        $modal = DB::table('payments as p')
            ->join('order_details as od', 'od.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->whereYear('p.payment_date', $year)
            ->selectRaw('MONTH(p.payment_date) as k, SUM(od.quantity * od.purchase_price_at_transaction) as v')
            ->groupByRaw('MONTH(p.payment_date)')
            ->pluck('v', 'k');

        $expenses = DB::table('expenses')
            ->whereYear('expense_date', $year)
            ->selectRaw('MONTH(expense_date) as k, SUM(amount) as v')
            ->groupByRaw('MONTH(expense_date)')
            ->pluck('v', 'k');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'label'    => $labels[$m - 1],
                'income'   => (float) ($income[$m] ?? 0),
                'modal'    => (float) ($modal[$m] ?? 0),
                'expenses' => (float) ($expenses[$m] ?? 0),
            ];
        }
        return $result;
    }

    private function chartYearly(): array
    {
        $income = DB::table('payments as p')
            ->join('orders as o', 'o.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->selectRaw('YEAR(p.payment_date) as k, SUM(o.total_amount) as v')
            ->groupByRaw('YEAR(p.payment_date)')
            ->pluck('v', 'k');

        $modal = DB::table('payments as p')
            ->join('order_details as od', 'od.order_id', '=', 'p.order_id')
            ->where('p.payment_status', 'paid')
            ->selectRaw('YEAR(p.payment_date) as k, SUM(od.quantity * od.purchase_price_at_transaction) as v')
            ->groupByRaw('YEAR(p.payment_date)')
            ->pluck('v', 'k');

        $expenses = DB::table('expenses')
            ->selectRaw('YEAR(expense_date) as k, SUM(amount) as v')
            ->groupByRaw('YEAR(expense_date)')
            ->pluck('v', 'k');

        $years = collect(
            array_unique(array_merge(
                $income->keys()->toArray(),
                $modal->keys()->toArray(),
                $expenses->keys()->toArray()
            ))
        )->sort()->values();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        $result = [];
        foreach ($years as $y) {
            $result[] = [
                'label'    => (string) $y,
                'income'   => (float) ($income[$y] ?? 0),
                'modal'    => (float) ($modal[$y] ?? 0),
                'expenses' => (float) ($expenses[$y] ?? 0),
            ];
        }
        return $result;
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

    public function favorites(Request $request)
    {
        $paidQuery = Payment::where('payment_status', 'paid');

        if ($request->start_date && $request->end_date) {
            $paidQuery->whereBetween('payment_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $paidOrderIds = $paidQuery->pluck('order_id');

        $data = DB::table('order_details as od')
            ->join('items as i', 'i.item_id', '=', 'od.item_id')
            ->join('categories as c', 'c.category_id', '=', 'i.category_id')
            ->join('item_types as t', 't.type_id', '=', 'c.type_id')
            ->whereIn('od.order_id', $paidOrderIds)
            ->select(
                'i.item_id',
                'i.item_name',
                'c.category_name',
                't.type_name',
                DB::raw('SUM(od.quantity) as total_qty'),
                DB::raw('SUM(od.quantity * od.selling_price_at_transaction) as total_revenue')
            )
            ->groupBy('i.item_id', 'i.item_name', 'c.category_name', 't.type_name')
            ->orderByDesc('total_qty')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Produk favorit berhasil diambil',
            'data' => $data,
        ]);
    }
}
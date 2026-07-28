<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->input('filter_type', 'today');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $now = Carbon::now();

        switch ($filterType) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'custom':
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay();
                break;
            default:
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
        }

        $ordersQuery = DB::table('orders')
            ->whereBetween('orders.created_at', [$start, $end]);

        $totalOrders = (clone $ordersQuery)->count();
        $totalRevenue = (clone $ordersQuery)->sum('final_amount');
        $cashRevenue = (clone $ordersQuery)->where('payment_method', 'cash')->sum('final_amount');
        $transferRevenue = (clone $ordersQuery)->where('payment_method', 'transfer')->sum('final_amount');

        $totalCost = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->sum(DB::raw('order_items.cost_price * order_items.quantity'));

        $grossProfit = $totalRevenue - $totalCost;

        $totalDebtGenerated = DB::table('debt_logs')
            ->where('transaction_type', 'borrow')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $lowStockProducts = DB::table('product_units')
            ->join('product_variants', 'product_units.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('product_units.is_base', true)
            ->where('product_units.stock_quantity', '<=', 10)
            ->select('products.name', 'product_variants.variant_name', 'product_units.unit_name', 'product_units.stock_quantity')
            ->limit(5)
            ->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        $chartDataRaw = (clone $ordersQuery)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(final_amount) as daily_revenue')
            )
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('daily_revenue', 'date')
            ->toArray();

        $chartLabels = [];
        $chartValues = [];
        $period = new \DatePeriod(
            new \DateTime($start->format('Y-m-d')),
            new \DateInterval('P1D'),
            (new \DateTime($end->format('Y-m-d')))->modify('+1 day')
        );

        foreach ($period as $dt) {
            $dateStr = $dt->format('Y-m-d');
            $chartLabels[] = $dt->format('d/m');
            $chartValues[] = $chartDataRaw[$dateStr] ?? 0;
        }

        $recentOrders = (clone $ordersQuery)
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.name as customer_name')
            ->orderBy('orders.id', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalRevenue',
            'grossProfit',
            'totalOrders',
            'cashRevenue',
            'transferRevenue',
            'totalDebtGenerated',
            'lowStockProducts',
            'topProducts',
            'recentOrders',
            'chartLabels',
            'chartValues',
            'filterType',
            'startDate',
            'endDate',
            'start',
            'end'
        ));
    }
}

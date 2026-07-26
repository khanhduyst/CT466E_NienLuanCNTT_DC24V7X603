<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('orders.invoice_number', 'LIKE', "%{$search}%")
                    ->orWhere('customers.name', 'LIKE', "%{$search}%")
                    ->orWhere('customers.phone_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('orders.created_at', '>=', $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('orders.created_at', '<=', $request->get('to_date'));
        }

        if ($request->filled('method')) {
            $query->where('orders.payment_method', $request->get('method'));
        }

        $statsQuery = clone $query;
        $stats = $statsQuery->select(
            DB::raw('SUM(orders.final_amount) as total_revenue'),
            DB::raw('SUM(orders.discount_amount) as total_discount'),
            DB::raw("SUM(CASE WHEN orders.payment_method = 'debt' THEN orders.final_amount ELSE 0 END) as total_debt")
        )->first();

        $orders = $query->select(
            'orders.*',
            'customers.name as customer_name',
            'users.name as user_name'
        )
            ->orderBy('orders.created_at', 'desc')
            ->paginate(4)
            ->withQueryString();

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show($id)
    {
        $order = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.id', $id)
            ->select('orders.*', 'customers.name as customer_name', 'users.name as user_name')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hóa đơn.'], 404);
        }

        $items = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_units', 'order_items.product_unit_id', '=', 'product_units.id')
            ->where('order_items.order_id', $id)
            ->select(
                'order_items.*',
                'products.name as product_name',
                'product_units.unit_name'
            )->get();

        return response()->json([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
    }
}

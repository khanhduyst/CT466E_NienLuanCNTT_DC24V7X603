<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('customers')->where('total_debt', '>', 0);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        $totalDebtSystem = DB::table('customers')->sum('total_debt');

        $debtors = $query->orderBy('total_debt', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('debts.index', compact('debtors', 'totalDebtSystem'));
    }

    public function getLogs($id)
    {
        $logs = DB::table('debt_logs')
            ->leftJoin('orders', 'debt_logs.order_id', '=', 'orders.id')
            ->where('debt_logs.customer_id', $id)
            ->select('debt_logs.*', 'orders.invoice_number')
            ->orderBy('debt_logs.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    public function payDebt(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255'
        ]);

        $customer = DB::table('customers')->where('id', $request->customer_id)->first();

        if ($request->amount > $customer->total_debt) {
            return response()->json([
                'success' => false, 
                'message' => 'Số tiền thu không được lớn hơn tổng số tiền khách đang nợ!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('customers')
                ->where('id', $request->customer_id)
                ->decrement('total_debt', $request->amount);

            $receiptNumber = 'PTN-' . time() . rand(10, 99);

            DB::table('debt_logs')->insert([
                'customer_id' => $request->customer_id,
                'order_id' => null,
                'transaction_type' => 'pay',
                'amount' => $request->amount,
                'note' => $request->note ? $request->note : "Khách thanh toán bớt nợ cũ",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ghi nhận thu tiền nợ khách hàng thành công!',
                'receipt_number' => $receiptNumber,
                'customer_name' => $customer->name,
                'old_debt' => $customer->total_debt,
                'amount_paid' => $request->amount,
                'remain_debt' => $customer->total_debt - $request->amount,
                'cashier' => Auth::user()->name
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
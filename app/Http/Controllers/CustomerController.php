<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // Trang danh sách khách hàng (Có phân trang chính)
    public function index(Request $request)
    {
        $query = DB::table('customers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone_number', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        // Phân trang danh sách khách hàng (10 khách/trang)
        $customers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    // Thêm mới khách hàng
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:customers,phone_number',
        ]);

        $barcode = $request->barcode ?? ('KH' . time());

        DB::table('customers')->insert([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'barcode' => $barcode,
            'current_points' => 0,
            'total_debt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Thêm khách hàng thành công!']);
    }

    // Cập nhật khách hàng
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15|unique:customers,phone_number,' . $id,
        ]);

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'barcode' => $request->barcode,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thông tin khách hàng thành công!']);
    }

    // Xóa khách hàng
    public function destroy($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Xóa khách hàng thành công!']);
    }

    // Lấy danh sách đơn mua (Phân trang AJAX 5 đơn/trang)
    public function getOrders($id)
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('orders')) {
                $orders = DB::table('orders')
                    ->where('customer_id', $id)
                    ->orderBy('id', 'desc')
                    ->paginate(5);

                return response()->json($orders);
            }
        } catch (\Exception $e) {
            // Trả về rỗng nếu chưa có dữ liệu/lỗi
        }

        return response()->json(['data' => []]);
    }

    // Lấy lịch sử công nợ (Phân trang AJAX 5 dòng/trang)
    public function getDebts($id)
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('debt_logs')) {
                $debts = DB::table('debt_logs')
                    ->where('customer_id', $id)
                    ->orderBy('id', 'desc')
                    ->paginate(5);

                return response()->json($debts);
            }
        } catch (\Exception $e) {
            // Trả về rỗng nếu chưa có dữ liệu/lỗi
        }

        return response()->json(['data' => []]);
    }

    public function getPoints($id)
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('point_logs')) {
                $points = DB::table('point_logs')
                    ->where('customer_id', $id)
                    ->orderBy('id', 'desc')
                    ->paginate(5); // 5 lịch sử / trang

                return response()->json($points);
            }
        } catch (\Exception $e) {
            // Lỗi query hoặc chưa có dữ liệu
        }

        return response()->json(['data' => []]);
    }
}

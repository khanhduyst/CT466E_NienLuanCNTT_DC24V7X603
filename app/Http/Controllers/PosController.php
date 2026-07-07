<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $customers = DB::table('customers')->select('id', 'name', 'phone_number')->get();
        return view('pos.index', compact('customers'));
    }

    public function searchProducts(Request $request)
    {
        $keyword = $request->get('keyword');

        $products = DB::table('products')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('product_units', 'product_variants.id', '=', 'product_units.product_variant_id')
            ->where('products.name', 'LIKE', "%{$keyword}%")
            ->orWhere('product_variants.barcode', 'LIKE', "%{$keyword}%")
            ->orWhere('product_units.unit_name', 'LIKE', "%{$keyword}%")
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_units.id as product_unit_id',
                'product_units.unit_name',
                'product_units.sale_price',
                'product_units.stock_quantity as current_stock'
            )->get();

        $products->transform(function ($item) {
            $formattedPrice = number_format($item->sale_price, 0, ',', '.') . 'đ';
            $item->product_display_name = "{$item->product_name} ({$item->unit_name} - {$formattedPrice})";
            return $item;
        });

        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'total_amount' => 'required|numeric',
            'discount_amount' => 'required|numeric',
            'final_amount' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'change_amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,qr_code,debt',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        DB::beginTransaction();
        try {
            $invoiceNumber = 'INV-' . strtoupper(Str::random(4)) . time();

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_id' => $request->customer_id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $request->total_amount,
                'discount_amount' => $request->discount_amount,
                'final_amount' => $request->final_amount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->change_amount,
                'payment_method' => $request->payment_method,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->payment_method === 'debt') {
                if (!$request->customer_id) {
                    throw new \Exception("Vui lòng chọn khách hàng cụ thể khi sử dụng hình thức Ghi nợ!");
                }

                DB::table('customers')
                    ->where('id', $request->customer_id)
                    ->increment('total_debt', $request->final_amount);

                DB::table('debt_logs')->insert([
                    'customer_id' => $request->customer_id,
                    'order_id' => $orderId,
                    'transaction_type' => DB::table('debt_logs')->where('id', 0)->first() ? 'debt' : 1,
                    'amount' => $request->final_amount,
                    'note' => "Ghi nợ đơn hàng - Hóa đơn số: " . $invoiceNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($request->cart as $item) {
                $unitInfo = DB::table('product_units')->where('id', $item['product_unit_id'])->first();

                if (!$unitInfo) {
                    throw new \Exception("Đơn vị tính không hợp lệ.");
                }

                if ($unitInfo->stock_quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm với ĐVT này đã hết hàng hoặc không đủ tồn kho!");
                }

                $costPrice = isset($unitInfo->import_price) ? $unitInfo->import_price : 0;

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $costPrice,
                    'sale_price' => $unitInfo->sale_price,
                    'subtotal' => $unitInfo->sale_price * $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_units')
                    ->where('id', $item['product_unit_id'])
                    ->decrement('stock_quantity', $item['quantity']);

                DB::table('inventory_logs')->insert([
                    'product_id' => $item['product_id'],
                    'user_id' => $userId,
                    'change_type' => 'sale',
                    'quantity' => $item['quantity'],
                    'note' => "Bán hàng - Hóa đơn số: " . $invoiceNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán hóa đơn thành công!',
                'invoice_number' => $invoiceNumber
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'barcode' => 'nullable|string|max:50',
        ]);

        $exists = DB::table('customers')->where('phone_number', $request->phone_number)->first();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Số điện thoại này đã tồn tại trên hệ thống!'], 422);
        }

        $barcode = $request->barcode ? $request->barcode : 'KH' . time();

        $customerId = DB::table('customers')->insertGetId([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'barcode' => $barcode,
            'total_debt' => 0,
            'current_points' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm khách hàng mới thành công!',
            'customer' => [
                'id' => $customerId,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'barcode' => $barcode,
                'total_debt' => 0
            ]
        ]);
    }
}

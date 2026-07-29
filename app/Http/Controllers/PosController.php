<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_deleted', 0)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->get();

        $products = Product::where('is_deleted', 0)
            ->with(['variants.units'])
            ->orderBy('id', 'desc')
            ->get();

        $customers = Customer::orderBy('name', 'asc')->get();

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();
        $pointConversionRate = (float)($settings['point_conversion_rate'] ?? 10000);
        $pointRedeemValue = (float)($settings['point_redeem_value'] ?? 100);
        $minOrderAmountForRedeem = (float)($settings['min_order_amount_for_redeem'] ?? 30000);
        $maxPointDiscountPercent = (float)($settings['max_point_discount_percent'] ?? 50);

        return view('pos.index', compact('categories', 'products', 'customers', 'pointConversionRate', 'pointRedeemValue', 'minOrderAmountForRedeem', 'maxPointDiscountPercent'));
    }

    public function quickStoreCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|regex:/^[0-9]{10,11}$/|unique:customers,phone_number',
            'barcode' => 'nullable|string|max:50|unique:customers,barcode',
        ], [
            'name.required' => 'Họ và tên khách hàng không được để trống.',
            'phone_number.required' => 'Số điện thoại không được để trống.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ (phải từ 10 đến 11 chữ số).',
            'phone_number.unique' => 'Số điện thoại này đã được đăng ký trên hệ thống.',
            'barcode.unique' => 'Mã vạch này đã tồn tại trên hệ thống.',
        ]);

        $barcode = $request->filled('barcode') ? $request->barcode : 'KH' . time();

        $customer = Customer::create([
            'name' => trim($request->name),
            'phone_number' => trim($request->phone_number),
            'barcode' => $barcode,
            'current_points' => 0,
            'total_debt' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm khách hàng mới thành công!',
            'customer' => $customer
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'payment_method' => 'required|in:cash,transfer',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id() ?? 1;
        $customerId = $request->customer_id ?? null;
        $discountAmount = (float)($request->discount_amount ?? 0);
        $isApplyPoints = (bool)($request->apply_points ?? false);

        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();
        $pointConversionRate = (float)($settings['point_conversion_rate'] ?? 10000);
        $pointRedeemValue = (float)($settings['point_redeem_value'] ?? 100);
        $minOrderAmountForRedeem = (float)($settings['min_order_amount_for_redeem'] ?? 30000);
        $maxPointDiscountPercent = (float)($settings['max_point_discount_percent'] ?? 50);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $unit = DB::table('product_units')->where('id', $item['unit_id'])->first();
                if (!$unit) {
                    throw new \Exception("Đơn vị tính không tồn tại!");
                }

                $qty = (int)$item['quantity'];
                $subtotal = $unit->sale_price * $qty;
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $unit->id,
                    'quantity' => $qty,
                    'cost_price' => $unit->import_price,
                    'sale_price' => $unit->sale_price,
                    'subtotal' => $subtotal,
                    'conversion_rate' => $unit->conversion_rate,
                    'variant_id' => $item['variant_id'],
                ];
            }

            $usePoints = 0;
            if ($isApplyPoints) {
                if (!$customerId) {
                    throw new \Exception("Vui lòng chọn khách hàng để áp dụng điểm tích lũy!");
                }

                if ($totalAmount < $minOrderAmountForRedeem) {
                    throw new \Exception("Đơn hàng tối thiểu từ " . number_format($minOrderAmountForRedeem) . "đ mới được đổi điểm!");
                }

                $customer = DB::table('customers')->where('id', $customerId)->first();
                $custPoints = (int)($customer->current_points ?? 0);

                if ($custPoints <= 0) {
                    throw new \Exception("Khách hàng không có điểm tích lũy!");
                }

                $maxByPercent = (int)floor($totalAmount * ($maxPointDiscountPercent / 100));
                $usePoints = min($custPoints, $maxByPercent);
            }

            $pointDiscountMoney = $usePoints;
            $totalDiscount = $discountAmount + $pointDiscountMoney;
            $finalAmount = max(0, $totalAmount - $totalDiscount);
            $paidAmount = (float)$request->paid_amount;

            $debtAmount = max(0, $finalAmount - $paidAmount);
            if ($debtAmount > 0) {
                if (!$customerId) {
                    throw new \Exception("Khách hàng còn thiếu " . number_format($debtAmount) . "đ. Bắt buộc phải chọn/tạo Khách hàng để ghi nợ!");
                }
                $changeAmount = 0;
            } else {
                $changeAmount = max(0, $paidAmount - $finalAmount);
            }

            $invoiceNumber = 'HD' . date('YmdHis') . rand(100, 999);

            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'final_amount' => $finalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($itemsData as $iData) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $iData['product_id'],
                    'product_unit_id' => $iData['product_unit_id'],
                    'quantity' => $iData['quantity'],
                    'cost_price' => $iData['cost_price'],
                    'sale_price' => $iData['sale_price'],
                    'subtotal' => $iData['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $deductStockQty = $iData['quantity'] * $iData['conversion_rate'];

                $baseUnit = DB::table('product_units')
                    ->where('product_variant_id', $iData['variant_id'])
                    ->where('is_base', true)
                    ->first();

                if ($baseUnit) {
                    DB::table('product_units')->where('id', $baseUnit->id)->decrement('stock_quantity', $deductStockQty);

                    DB::table('inventory_logs')->insert([
                        'product_id' => $iData['product_id'],
                        'user_id' => $userId,
                        'change_type' => 'export',
                        'quantity' => -$deductStockQty,
                        'note' => "Xuất kho bán hàng qua hóa đơn #{$invoiceNumber}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($customerId) {
                if ($usePoints > 0) {
                    DB::table('customers')->where('id', $customerId)->decrement('current_points', $usePoints);

                    DB::table('point_logs')->insert([
                        'customer_id' => $customerId,
                        'order_id' => $orderId,
                        'change_type' => 'redeem',
                        'points' => $usePoints,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($pointConversionRate > 0 && $finalAmount >= $pointConversionRate) {
                    $earnedPoints = (int)floor($finalAmount / $pointConversionRate) * (int)$pointRedeemValue;
                    if ($earnedPoints > 0) {
                        DB::table('customers')->where('id', $customerId)->increment('current_points', $earnedPoints);

                        DB::table('point_logs')->insert([
                            'customer_id' => $customerId,
                            'order_id' => $orderId,
                            'change_type' => 'earn',
                            'points' => $earnedPoints,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($debtAmount > 0) {
                    DB::table('customers')->where('id', $customerId)->increment('total_debt', $debtAmount);

                    DB::table('debt_logs')->insert([
                        'customer_id' => $customerId,
                        'order_id' => $orderId,
                        'transaction_type' => 'borrow',
                        'amount' => $debtAmount,
                        'note' => "Khách thiếu tiền/Ghi nợ từ hóa đơn #{$invoiceNumber}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => "Xuất hóa đơn thành công #{$invoiceNumber} (Tổng: " . number_format($finalAmount) . "đ)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công!',
                'invoice_number' => $invoiceNumber,
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

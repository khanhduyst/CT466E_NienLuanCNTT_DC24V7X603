<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();

        $products = DB::table('products')
            ->where('is_deleted', false)
            ->select('id', 'name', 'barcode')
            ->get();

        return view('admin.purchase', compact('suppliers', 'products'));
    }

    public function storeSupplier(Request $request)
    {
        Supplier::create([
            'name' => $request->name,
            'barcode' => 'NCC_' . strtoupper(uniqid()),
            'phone_number' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'total_debt' => 0,
        ]);

        return redirect()->back()->with('success', 'Thêm nhà cung cấp mới thành công!');
    }

    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return redirect()->back()->with('success', 'Cập nhật nhà cung cấp thành công!');
    }

    public function destroySupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->back()->with('success', 'Xóa nhà cung cấp thành công!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'items' => 'required|array',
        ]);

        $batchCode = $request->batch_code;

        if (empty($batchCode)) {
            $batchCode = 'LH-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        }

        $userId = Auth::id() ?? 1;

        DB::transaction(function () use ($request, $batchCode, $userId) {
            foreach ($request->items as $productId => $item) {

                DB::table('batches')->insert([
                    'product_id' => $productId,
                    'supplier_id' => $request->supplier_id,
                    'batch_code' => $batchCode,
                    'original_quantity' => $item['quantity'],
                    'current_quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'manufacture_date' => $item['manufacture_date'],
                    'expiry_date' => $item['expiry_date'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $variantId = DB::table('product_variants')
                    ->where('product_id', $productId)
                    ->value('id');

                if ($variantId) {
                    $currentUnit = DB::table('product_units')
                        ->where('product_variant_id', $variantId)
                        ->first();

                    if ($currentUnit) {
                        $oldStock = $currentUnit->stock_quantity ?? 0;
                        $oldPrice = $currentUnit->import_price ?? 0;

                        $newStock = $item['quantity'];
                        $newPrice = $item['purchase_price'];

                        if (($oldStock + $newStock) > 0) {
                            $averagePrice = (($oldStock * $oldPrice) + ($newStock * $newPrice)) / ($oldStock + $newStock);
                        } else {
                            $averagePrice = $newPrice;
                        }

                        DB::table('product_units')
                            ->where('product_variant_id', $variantId)
                            ->update([
                                'stock_quantity' => $oldStock + $newStock,
                                'import_price' => $averagePrice,
                                'updated_at' => now()
                            ]);

                        DB::table('inventory_logs')->insert([
                            'product_id'   => $productId,
                            'user_id'      => $userId,
                            'change_type'  => 'import',
                            'quantity'     => $newStock,
                            'note'         => 'Nhập kho tự động theo phiếu nhập: ' . $batchCode,
                            'created_at'   => now(),
                            'updated_at'   => now()
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.purchase.history')->with('success', 'Nhập kho, tính toán lại giá vốn và ghi nhật ký kho thành công!');
    }

    public function indexSupplier()
    {
        $suppliers = Supplier::all();
        return view('admin.supplier_index', compact('suppliers'));
    }

    public function history()
    {
        $batches = DB::table('batches')
            ->join('suppliers', 'batches.supplier_id', '=', 'suppliers.id')
            ->select(
                'batches.batch_code',
                'suppliers.name as supplier_name',
                'batches.created_at',
                DB::raw('SUM(batches.original_quantity) as total_quantity'),
                DB::raw('SUM(batches.original_quantity * batches.purchase_price) as total_amount')
            )
            ->groupBy('batches.batch_code', 'suppliers.name', 'batches.created_at')
            ->orderBy('batches.created_at', 'desc')
            ->get();

        foreach ($batches as $batch) {
            $batch->details = DB::table('batches')
                ->join('products', 'batches.product_id', '=', 'products.id')
                ->where('batches.batch_code', $batch->batch_code)
                ->select(
                    'products.name as product_name',
                    'batches.original_quantity',
                    'batches.purchase_price',
                    'batches.manufacture_date',
                    'batches.expiry_date'
                )
                ->get();
        }

        return view('admin.purchase_history', compact('batches'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_deleted', 0)->get();
        // Lấy danh sách Nhà cung cấp
        $suppliers = DB::table('suppliers')->orderBy('name', 'asc')->get();

        $query = Product::with(['category', 'variants.units']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('variants', function ($vQ) use ($search) {
                        $vQ->where('variant_name', 'LIKE', "%{$search}%")
                            ->orWhere('barcode', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('parent_category_id')) {
            $childIds = Category::where('parent_id', $request->parent_category_id)->pluck('id')->toArray();
            $childIds[] = $request->parent_category_id;
            $query->whereIn('category_id', $childIds);
        }

        $products = $query->orderBy('id', 'desc')->paginate(5)->withQueryString();

        // Gắn thêm supplier_id từ lô hàng (batches) mới nhất vào từng sản phẩm
        foreach ($products as $p) {
            $latestBatch = DB::table('batches')->where('product_id', $p->id)->orderBy('id', 'desc')->first();
            $p->supplier_id = $latestBatch ? $latestBatch->supplier_id : null;
        }

        return view('admin.product', compact('products', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.variant_name' => 'required|string|max:255',
            'variants.*.base_unit' => 'required|string|max:255',
            'variants.*.import_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0',
            'variants.*.stock_quantity' => 'required|integer|min:0',
        ], [
            'name.required' => 'Tên sản phẩm chính không được để trống.',
            'category_id.required' => 'Vui lòng chọn danh mục chi tiết cho sản phẩm.',
            'supplier_id.exists' => 'Nhà cung cấp được chọn không hợp lệ.',
            'variants.required' => 'Sản phẩm phải có ít nhất một biến thể.',
            'variants.*.variant_name.required' => 'Tên thuộc tính hoặc dung tích của biến thể không được để trống.',
            'variants.*.base_unit.required' => 'Đơn vị tính gốc không được để trống.',
            'variants.*.import_price.required' => 'Giá nhập gốc không được để trống.',
            'variants.*.sale_price.required' => 'Giá bán lẻ gốc không được để trống.',
            'variants.*.stock_quantity.required' => 'Số lượng tồn kho ban đầu không được để trống.',
        ]);

        $userId = Auth::id() ?? 1;

        DB::transaction(function () use ($request, $userId) {
            $imageName = null;
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $request->file('image')->move(public_path('uploads/products'), $imageName);
            }

            // 1. Tạo sản phẩm chính
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'image' => $imageName,
            ]);

            $productId = $product->id;

            // Log hoạt động
            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => "Thêm mới sản phẩm chính: {$product->name} (ID: {$productId})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalInitialStock = 0;
            $firstImportPrice = 0;

            // 2. Tạo biến thể & đơn vị tính
            foreach ($request->variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $productId,
                    'variant_name' => $vData['variant_name'],
                    'barcode' => $vData['barcode'] ?? null,
                ]);

                $variantId = $variant->id;
                $stockQty = $vData['stock_quantity'] ?? 0;
                $totalInitialStock += $stockQty;
                $firstImportPrice = $vData['import_price'] ?? 0;

                ProductUnit::create([
                    'product_variant_id' => $variantId,
                    'unit_name' => $vData['base_unit'],
                    'conversion_rate' => 1,
                    'import_price' => $vData['import_price'],
                    'sale_price' => $vData['sale_price'],
                    'stock_quantity' => $stockQty,
                    'is_base' => true,
                ]);

                if ($stockQty > 0) {
                    DB::table('inventory_logs')->insert([
                        'product_id' => $productId,
                        'user_id' => $userId,
                        'change_type' => 'import',
                        'quantity' => $stockQty,
                        'note' => "Khởi tạo tồn kho ban đầu cho biến thể [{$vData['variant_name']}]",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if (isset($vData['conversions']) && is_array($vData['conversions'])) {
                    foreach ($vData['conversions'] as $cData) {
                        if (!empty($cData['unit_name']) && !empty($cData['sale_price'])) {
                            ProductUnit::create([
                                'product_variant_id' => $variantId,
                                'unit_name' => $cData['unit_name'],
                                'conversion_rate' => $cData['conversion_rate'] ?? 1,
                                'import_price' => $vData['import_price'] * ($cData['conversion_rate'] ?? 1),
                                'sale_price' => $cData['sale_price'],
                                'stock_quantity' => 0,
                                'is_base' => false,
                            ]);
                        }
                    }
                }
            }

            // 3. Nếu chọn Nhà cung cấp, TỰ ĐỘNG TẠO BẢN GHI LÔ HÀNG (batches)
            if ($request->supplier_id) {
                DB::table('batches')->insert([
                    'product_id' => $productId,
                    'supplier_id' => $request->supplier_id,
                    'batch_code' => 'BAT_' . date('Ymd') . '_' . time(),
                    'original_quantity' => $totalInitialStock,
                    'current_quantity' => $totalInitialStock,
                    'purchase_price' => $firstImportPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Thêm sản phẩm thành công!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $userId = Auth::id() ?? 1;

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $productId = $product->id;

            if ($request->remove_current_image == "1") {
                if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
                    unlink(public_path('uploads/products/' . $product->image));
                }
                $product->image = null;
            }

            if ($request->hasFile('image')) {
                if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
                    unlink(public_path('uploads/products/' . $product->image));
                }
                $extension = $request->file('image')->getClientOriginalExtension();
                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $request->file('image')->move(public_path('uploads/products'), $imageName);
                $product->image = $imageName;
            }

            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'image' => $product->image,
            ]);

            // Cập nhật hoặc Thêm mới Nhà cung cấp vào lô hàng (batches)
            if ($request->supplier_id) {
                $latestBatch = DB::table('batches')->where('product_id', $productId)->orderBy('id', 'desc')->first();
                if ($latestBatch) {
                    DB::table('batches')->where('id', $latestBatch->id)->update([
                        'supplier_id' => $request->supplier_id,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('batches')->insert([
                        'product_id' => $productId,
                        'supplier_id' => $request->supplier_id,
                        'batch_code' => 'BAT_' . date('Ymd') . '_' . time(),
                        'original_quantity' => 0,
                        'current_quantity' => 0,
                        'purchase_price' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => "Cập nhật thông tin sản phẩm: {$product->name} (ID: {$productId})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->has('variants')) {
                foreach ($request->variants as $vIndex => $vData) {
                    if (isset($vData['id'])) {
                        $variant = ProductVariant::find($vData['id']);
                        if ($variant) {
                            $variantId = $variant->id;

                            $variant->update([
                                'variant_name' => $vData['variant_name'],
                                'barcode' => $vData['barcode'] ?? null,
                            ]);

                            $oldBaseUnit = DB::table('product_units')
                                ->where('product_variant_id', $variantId)
                                ->where('is_base', true)
                                ->first();

                            $oldStock = $oldBaseUnit ? $oldBaseUnit->stock_quantity : 0;
                            $newStock = $vData['stock_quantity'] ?? 0;

                            DB::table('product_units')
                                ->where('product_variant_id', $variantId)
                                ->where('is_base', true)
                                ->update([
                                    'unit_name'       => $vData['base_unit'],
                                    'import_price'    => $vData['import_price'] ?? 0,
                                    'sale_price'      => $vData['sale_price'] ?? 0,
                                    'stock_quantity'  => $newStock,
                                    'updated_at'      => now(),
                                ]);

                            $keepUnitNames = [$vData['base_unit']];

                            if (isset($vData['conversions'])) {
                                foreach ($vData['conversions'] as $cData) {
                                    if (!empty($cData['unit_name'])) {
                                        $keepUnitNames[] = $cData['unit_name'];

                                        $exists = DB::table('product_units')
                                            ->where('product_variant_id', $variantId)
                                            ->where('unit_name', $cData['unit_name'])
                                            ->where('is_base', false)
                                            ->first();

                                        if ($exists) {
                                            DB::table('product_units')->where('id', $exists->id)->update([
                                                'conversion_rate' => $cData['conversion_rate'] ?? 1,
                                                'import_price'    => $vData['import_price'] ?? 0,
                                                'sale_price'      => $cData['sale_price'] ?? 0,
                                                'updated_at'      => now(),
                                            ]);
                                        } else {
                                            ProductUnit::create([
                                                'product_variant_id' => $variantId,
                                                'unit_name'          => $cData['unit_name'],
                                                'conversion_rate'    => $cData['conversion_rate'] ?? 1,
                                                'import_price'       => $vData['import_price'] ?? 0,
                                                'sale_price'         => $cData['sale_price'] ?? 0,
                                                'stock_quantity'     => 0,
                                                'is_base'            => false,
                                            ]);
                                        }
                                    }
                                }
                            }

                            $oldUnits = DB::table('product_units')
                                ->where('product_variant_id', $variantId)
                                ->whereNotIn('unit_name', $keepUnitNames)
                                ->get();

                            foreach ($oldUnits as $oldUnit) {
                                $hasOrder = DB::table('order_items')->where('product_unit_id', $oldUnit->id)->exists();
                                if ($hasOrder) {
                                    DB::rollBack();
                                    return redirect()->back()->withErrors(['error' => "Không thể xóa hoặc đổi tên đơn vị tính '{$oldUnit->unit_name}' của biến thể '{$vData['variant_name']}' vì đơn vị này đã phát sinh lịch sử hóa đơn bán hàng!"]);
                                }
                                DB::table('product_units')->where('id', $oldUnit->id)->delete();
                            }

                            if ($oldStock != $newStock) {
                                $diff = $newStock - $oldStock;
                                DB::table('inventory_logs')->insert([
                                    'product_id'   => $productId,
                                    'user_id'      => $userId,
                                    'change_type'  => 'adjustment',
                                    'quantity'     => $diff,
                                    'note'         => "Thay đổi tồn kho thủ công từ [{$oldStock}] thành [{$newStock}] tại biến thể [{$vData['variant_name']}]",
                                    'created_at'   => now(),
                                    'updated_at'   => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            if ($request->has('new_variants')) {
                foreach ($request->new_variants as $nvData) {
                    if (!empty($nvData['variant_name'])) {
                        $newVariant = ProductVariant::create([
                            'product_id'   => $productId,
                            'variant_name' => $nvData['variant_name'],
                            'barcode'      => $nvData['barcode'] ?? null,
                        ]);

                        $newVariantId = $newVariant->id;

                        ProductUnit::create([
                            'product_variant_id' => $newVariantId,
                            'unit_name'          => $nvData['base_unit'],
                            'conversion_rate'    => 1,
                            'import_price'       => $nvData['import_price'] ?? 0,
                            'sale_price'         => $nvData['sale_price'] ?? 0,
                            'stock_quantity'     => $nvData['stock_quantity'] ?? 0,
                            'is_base'            => true,
                        ]);

                        if (($nvData['stock_quantity'] ?? 0) > 0) {
                            DB::table('inventory_logs')->insert([
                                'product_id'   => $productId,
                                'user_id'      => $userId,
                                'change_type'  => 'import',
                                'quantity'     => $nvData['stock_quantity'],
                                'note'         => "Khởi tạo tồn kho ban đầu cho biến thể mới thêm [{$nvData['variant_name']}]",
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Cập nhật thông tin sản phẩm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $userId = Auth::id() ?? 1;
        $product = Product::findOrFail($id);

        $productId = $product->id;

        DB::transaction(function () use ($product, $productId, $userId) {
            $product->update(['is_deleted' => true]);

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => "Xóa sản phẩm (Soft Delete): {$product->name} (ID: {$productId})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Đã xóa sản phẩm thành công!');
    }
}
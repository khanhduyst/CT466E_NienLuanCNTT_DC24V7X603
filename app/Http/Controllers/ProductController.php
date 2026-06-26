<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Đã thêm use Auth chuẩn chỉnh

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_deleted', 0)->get();
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

        return view('admin.product', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
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
            'variants.required' => 'Sản phẩm phải có ít nhất một biến thể.',
            'variants.*.variant_name.required' => 'Tên thuộc tính hoặc dung tích của biến thể không được để trống.',
            'variants.*.base_unit.required' => 'Đơn vị tính gốc không được để trống.',
            'variants.*.import_price.required' => 'Giá nhập gốc không được để trống.',
            'variants.*.sale_price.required' => 'Giá bán lẻ gốc không được để trống.',
            'variants.*.stock_quantity.required' => 'Số lượng tồn kho ban đầu không được để trống.',
        ]);

        $userId = Auth::id() ?? 1; // Sử dụng Auth::id() sạch sẽ không bị gạch đỏ

        DB::transaction(function () use ($request, $userId) {
            $imageName = null;
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $request->file('image')->move(public_path('uploads/products'), $imageName);
            }

            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'image' => $imageName,
            ]);

            /** @var \App\Models\Product $product */
            $productId = $product->id; // Định danh giúp VS Code không gạch đỏ lỗi ID giả

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'action' => "Thêm mới sản phẩm chính: {$product->name} (ID: {$productId})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $productId,
                    'variant_name' => $vData['variant_name'],
                    'barcode' => $vData['barcode'] ?? null,
                ]);

                /** @var \App\Models\ProductVariant $variant */
                $variantId = $variant->id;

                ProductUnit::create([
                    'product_variant_id' => $variantId,
                    'unit_name' => $vData['base_unit'],
                    'conversion_rate' => 1,
                    'import_price' => $vData['import_price'],
                    'sale_price' => $vData['sale_price'],
                    'stock_quantity' => $vData['stock_quantity'],
                    'is_base' => true,
                ]);

                if ($vData['stock_quantity'] > 0) {
                    DB::table('inventory_logs')->insert([
                        'product_id' => $productId,
                        'user_id' => $userId,
                        'change_type' => 'import',
                        'quantity' => $vData['stock_quantity'],
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
        });

        return redirect()->back()->with('success', 'Thêm sản phẩm đa biến thể thành công!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $userId = Auth::id() ?? 1;

        DB::transaction(function () use ($request, $id, $userId) {
            $product = Product::findOrFail($id);

            /** @var \App\Models\Product $product */
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
                            /** @var \App\Models\ProductVariant $variant */
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

                            $variant->units()->delete();

                            ProductUnit::create([
                                'product_variant_id' => $variantId,
                                'unit_name'          => $vData['base_unit'],
                                'conversion_rate'    => 1,
                                'import_price'       => $vData['import_price'] ?? 0,
                                'sale_price'         => $vData['sale_price'] ?? 0,
                                'stock_quantity'     => $newStock,
                                'is_base'            => true,
                            ]);

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

                            if (isset($vData['conversions'])) {
                                foreach ($vData['conversions'] as $cData) {
                                    if (!empty($cData['unit_name'])) {
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

                        /** @var \App\Models\ProductVariant $newVariant */
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
        });

        return redirect()->back()->with('success', 'Cập nhật thông tin sản phẩm và cấu trúc biến thể thành công!');
    }

    public function destroy($id)
    {
        $userId = Auth::id() ?? 1;
        $product = Product::findOrFail($id);

        /** @var \App\Models\Product $product */
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

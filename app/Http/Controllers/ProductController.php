<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($request) {
            $imageName = null;
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('uploads/products'), $imageName);
            }

            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'image' => $imageName,
            ]);

            foreach ($request->variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_name' => $vData['variant_name'],
                    'barcode' => $vData['barcode'] ?? null,
                ]);

                ProductUnit::create([
                    'product_variant_id' => $variant->id,
                    'unit_name' => $vData['base_unit'],
                    'conversion_rate' => 1,
                    'import_price' => $vData['import_price'],
                    'sale_price' => $vData['sale_price'],
                    'stock_quantity' => $vData['stock_quantity'],
                    'is_base' => true,
                ]);

                if (isset($vData['conversions']) && is_array($vData['conversions'])) {
                    foreach ($vData['conversions'] as $cData) {
                        if (!empty($cData['unit_name']) && !empty($cData['sale_price'])) {
                            ProductUnit::create([
                                'product_variant_id' => $variant->id,
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
            'barcode' => 'nullable|string|max:255',
            'base_unit' => 'required|string|max:255',
            'base_sale_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $id) {
            $product = Product::findOrFail($id);


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
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('uploads/products'), $imageName);
                $product->image = $imageName;
            }


            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'barcode' => $request->barcode,
                'image' => $product->image,
            ]);


            $product->units()->delete();


            ProductUnit::create([
                'product_id' => $product->id,
                'unit_name' => $request->base_unit,
                'conversion_rate' => 1,
                'base_price' => $request->base_sale_price,
                'sale_price' => $request->base_sale_price,
                'is_base' => true,
            ]);


            if ($request->has('units')) {
                foreach ($request->units as $unit) {
                    if (!empty($unit['unit_name']) && !empty($unit['sale_price'])) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_name' => $unit['unit_name'],
                            'conversion_rate' => $unit['conversion_rate'] ?? 1,
                            'base_price' => $unit['sale_price'],
                            'sale_price' => $unit['sale_price'],
                            'is_base' => false,
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_deleted' => true]);

        return redirect()->back()->with('success', 'Đã xóa sản phẩm thành công!');
    }
}

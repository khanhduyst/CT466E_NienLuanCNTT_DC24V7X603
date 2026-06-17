<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_deleted', false)->with(['category', 'units'])->get();
        $categories = Category::where('is_deleted', false)->get();

        return view('admin.product', compact('products', 'categories'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'barcode' => 'nullable|string|max:255',
            'base_unit' => 'required|string|max:255',
            'base_sale_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
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
                'barcode' => $request->barcode,
                'image' => $imageName,
            ]);


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

        return redirect()->back()->with('success', 'Thêm sản phẩm và hình ảnh thành công!');
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

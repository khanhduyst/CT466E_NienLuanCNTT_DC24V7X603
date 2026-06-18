<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $allCategories = Category::where('is_deleted', false)->with('parent')->get();
        $parentCategories = Category::where('is_deleted', false)->whereNull('parent_id')->get();

        $sortedCategories = collect();
        foreach ($parentCategories as $parent) {
            $sortedCategories->push($parent);

            $children = $allCategories->where('parent_id', $parent->id);
            foreach ($children as $child) {
                $sortedCategories->push($child);
            }
        }

        return view('admin.category', [
            'categories' => $sortedCategories,
            'parentCategories' => $parentCategories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
        ], [
            'name.unique' => 'Tên danh mục này đã tồn tại trong hệ thống!',
            'name.required' => 'Vui lòng nhập tên danh mục!',
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->back()->with('success', 'Thêm danh mục mới thành công!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
        ],[
            'name.unique' => 'Tên danh mục này đã tồn tại trong hệ thống!',
            'name.required' => 'Vui lòng nhập tên danh mục!',
        ]);

        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->back()->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->update(['is_deleted' => true]);

        return redirect()->back()->with('success', 'Đã xóa (ẩn) danh mục thành công!');
    }
}

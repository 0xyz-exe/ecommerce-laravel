<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['nullable', 'alpha_dash', 'max:150', 'unique:categories,slug']]);
        Category::create(['name' => $data['name'], 'slug' => $data['slug'] ?: Str::slug($data['name'])]);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'alpha_dash', 'max:150', 'unique:categories,slug,'.$category->id]]);
        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category removed. Products remain uncategorized.');
    }
}

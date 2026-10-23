<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', ['products' => Product::with('category')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) $data['image_path'] = $request->file('image')->store('products', 'public');
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image')) $data['image_path'] = $request->file('image')->store('products', 'public');
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return back()->with('success', 'Product archived.');
    }
}

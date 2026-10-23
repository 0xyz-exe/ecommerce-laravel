<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::query()->where('is_active', true)->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q')->toString().'%'))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category')->toString())))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('storefront.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('storefront.show', compact('product'));
    }

    public function cart(): View
    {
        $cart = session('cart', []);
        $products = Product::query()->whereIn('id', array_keys($cart))->where('is_active', true)->get()->keyBy('id');
        $total = $products->sum(fn (Product $product) => $product->price * min((int) ($cart[$product->id] ?? 0), $product->stock));

        return view('storefront.cart', compact('cart', 'products', 'total'));
    }

    public function addToCart(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.$product->stock]]);
        $cart = session('cart', []);
        $quantity = (int) ($cart[$product->id] ?? 0) + $data['quantity'];
        abort_if($quantity > $product->stock, 422, 'Requested quantity exceeds available stock.');
        $cart[$product->id] = $quantity;
        session(['cart' => $cart]);

        return back()->with('success', 'Product added to cart.');
    }

    public function updateCart(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:'.$product->stock]]);
        $cart = session('cart', []);
        if ($data['quantity'] === 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $data['quantity'];
        }
        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated.');
    }
}

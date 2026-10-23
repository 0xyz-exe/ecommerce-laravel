<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(): View
    {
        if (empty(session('cart', []))) {
            return redirect()->route('cart.index');
        }

        return view('storefront.checkout');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:120'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
        ]);
        $cart = session('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index');
        }

        $order = DB::transaction(function () use ($cart, $data): Order {
            $products = Product::query()->whereIn('id', array_keys($cart))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_if($products->count() !== count($cart), 422, 'A cart item is no longer available.');

            $total = 0;
            foreach ($cart as $id => $quantity) {
                $product = $products->get($id);
                abort_unless($product && $product->is_active && $quantity > 0 && $quantity <= $product->stock, 422, 'Insufficient stock.');
                $total += $product->price * $quantity;
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-'.Str::upper(Str::random(12)),
                'total' => $total,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'pending',
                ...$data,
            ]);

            foreach ($cart as $id => $quantity) {
                $product = $products->get($id);
                $order->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->price]);
                $product->decrement('stock', $quantity);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('orders.show', $order)->with('success', 'Order placed. Payment due on delivery.');
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        return view('storefront.order', ['order' => $order->load('items.product')]);
    }

    public function history(): View
    {
        return view('storefront.orders', ['orders' => auth()->user()->orders()->latest()->paginate(15)]);
    }
}

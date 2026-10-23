<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_with_server_price_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Kitchen', 'slug' => 'kitchen']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Cup', 'slug' => 'cup', 'price' => 1250, 'stock' => 5, 'is_active' => true]);

        $response = $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->post('/checkout', [
            'shipping_name' => 'Test Customer', 'shipping_address' => '1 Main St', 'shipping_city' => 'Springfield', 'shipping_postal_code' => '12345',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(2500, $order->total);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1250]);
        $this->assertNull(session('cart'));
    }

    public function test_checkout_rejects_insufficient_stock_without_partial_order(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Cup', 'slug' => 'cup', 'price' => 1250, 'stock' => 1, 'is_active' => true]);

        $response = $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->post('/checkout', [
            'shipping_name' => 'Test Customer', 'shipping_address' => '1 Main St', 'shipping_city' => 'Springfield', 'shipping_postal_code' => '12345',
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::create(['user_id' => $owner->id, 'order_number' => 'ORD-TEST123', 'total' => 100, 'shipping_name' => 'Owner', 'shipping_address' => '1 Street', 'shipping_city' => 'Town', 'shipping_postal_code' => '123']);

        $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
    }
}

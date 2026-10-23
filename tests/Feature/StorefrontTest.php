<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_lists_active_products_and_searches(): void
    {
        $active = Product::create(['name' => 'Linen Towel', 'slug' => 'linen-towel', 'price' => 1200, 'stock' => 3, 'is_active' => true]);
        Product::create(['name' => 'Hidden Cup', 'slug' => 'hidden-cup', 'price' => 800, 'stock' => 2, 'is_active' => false]);

        $this->get('/')->assertOk()->assertSee('Linen Towel')->assertDontSee('Hidden Cup');
        $this->get('/?q=linen')->assertOk()->assertSee('Linen Towel');
        $this->get('/?q=nonexistent')->assertOk()->assertDontSee('Linen Towel');
        $this->get(route('products.show', $active))->assertOk()->assertSee('Linen Towel');
    }

    public function test_cart_rejects_quantity_above_available_stock(): void
    {
        $product = Product::create(['name' => 'Linen Towel', 'slug' => 'linen-towel', 'price' => 1200, 'stock' => 2, 'is_active' => true]);

        $this->from('/')->post(route('cart.add', $product), ['quantity' => 3])->assertSessionHasErrors('quantity');
    }

    public function test_admin_routes_require_server_side_admin_authorization(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->actingAs(\App\Models\User::factory()->create())->get(route('admin.products.index'))->assertForbidden();
    }
}

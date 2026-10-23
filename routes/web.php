<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('products.show');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart.index');
Route::post('/cart/{product}', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::patch('/cart/{product}', [StorefrontController::class, 'updateCart'])->name('cart.update');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [CheckoutController::class, 'history'])->name('orders.index');
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\CartController;

// Public Shop Routes
Route::get('/', [ShopController::class, 'index'])->name('shop.home');
Route::get('/catalog', [ShopController::class, 'catalog'])->name('shop.catalog');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('shop.product');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('shop.cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');


// Admin SPA Route
Route::get('/admin/{any?}', function () {
    return view('admin.index');
})->where('any', '.*');

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ShopifyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. The entry point (Matches your 'App URL' in Shopify Dashboard)
Route::get('/install', [ShopifyController::class, 'install'])->name('shopify.install');

// 2. The handshake/callback (Matches your 'Allowed redirection URL' in Shopify Dashboard)
Route::get('/auth/callback', [ShopifyController::class, 'callback'])->name('shopify.callback');

Route::get('/', [ShopifyController::class, 'index'])->name('home');

Route::get('/products/{shop}', [ShopifyController::class, 'products'])->name('shopify.products');
Route::post('/products/store', [ShopifyController::class, 'storeProduct'])->name('shopify.product.store');
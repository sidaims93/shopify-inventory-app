<?php

use App\Http\Controllers\CheckoutUIExtensionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\DevRantController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ThemeAppExtensionController;
use App\Http\Controllers\WebhooksController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('shopify.orders');
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('shopify.products');
        Route::get('collections', [ProductController::class, 'productCollections'])->name('shopify.product.collections');
        Route::get('show', [ProductController::class, 'showProductData'])->name('shopify.product.show');
    });

    Route::prefix('inventories')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('shopify.inventories');
    });
    
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('sales/card', [DashboardController::class, 'salesCardInfo'])->name('dashboard.sales.filter');
        Route::get('recent/activity', [DashboardController::class, 'recentActivity'])->name('recent.activity');
    });

    Route::get('checkStoreSetup', [HomeController::class, 'checkStoreSetup'])->name('check.store.setup');
});

//App Installation Routes
Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationController::class, 'startInstallation'])->name('shopify.auth');
    Route::get('redirect', [InstallationController::class, 'handleRedirect'])->name('shopify.auth.redirect');
});

//For Tayyab, delete this later lol
Route::get('setAppMetafield', [ThemeAppExtensionController::class, 'setAppMetafield'])->name('set.app.metafield');

//For Jayden, remove this later lol
Route::prefix('checkout/extensions')->middleware('cors')->group(function () {
    Route::post('test', [CheckoutUIExtensionController::class, 'testAPI'])->name('checkout.ui.extension');
    Route::get('frosty', [CheckoutUIExtensionController::class, 'checkFrosty'])->name('checkout.ui.frosty');
});

//Mandatory GDPR Webhooks
Route::prefix('gdpr/webhooks')->group(function () {
    Route::any('customer_data_request', [WebhooksController::class, 'handleCustomerDataRequest']);
    Route::any('customer_data_erasure', [WebhooksController::class, 'handleCustomerDataErasure']);
    Route::any('shop_data_erasure', [WebhooksController::class, 'handleShopDataErasure']);
});









//DEVRANT Routes. Delete this later lol.
Route::prefix('devRant')->group(function () {
    Route::get('login', [DevRantController::class, 'login'])->name('devRant.login');
    Route::post('login', [DevRantController::class, 'submitLogin'])->name('devRant.submitLogin');

    Route::middleware('auth')->group(function () {
        Route::get('rants', [DevRantController::class, 'getRants'])->name('devRant.getRants');
        Route::post('postRant', [DevRantController::class, 'postRant'])->name('devRant.postRant');
        Route::get('rant', [DevRantController::class, 'showRant'])->name('devRant.showRant');
        Route::get('me', [DevRantController::class, 'viewProfile'])->name('devRant.me');
        Route::get('profile/{id}', [DevRantController::class, 'viewCustomProfile'])->name('devRant.show.custom.profile');
        Route::get('search/username', [DevRantController::class, 'searchUserByUsername'])->name('devRant.search.by.username');
    });
});
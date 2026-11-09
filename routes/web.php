<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
    Route::resource('products', ProductController::class)->except(['show']);

    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    Route::post('purchases/{purchase}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    Route::resource('purchases', PurchaseOrderController::class)
        ->parameters(['purchases' => 'purchase'])
        ->except(['edit', 'update']);

    Route::post('sales/{sale}/ship', [SalesOrderController::class, 'ship'])->name('sales.ship');
    Route::resource('sales', SalesOrderController::class)
        ->parameters(['sales' => 'sale'])
        ->except(['edit', 'update']);

    Route::resource('movements', StockMovementController::class)->only(['index']);
});

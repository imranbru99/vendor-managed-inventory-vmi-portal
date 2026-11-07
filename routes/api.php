<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\ReplenishmentProposalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'tenant.context', 'throttle:120,1'])->group(function (): void {
        Route::post('auth/logout', [AuthTokenController::class, 'destroy']);

        Route::middleware('module.enabled:catalog')->group(function (): void {
            Route::get('products', [ProductController::class, 'index']);
            Route::get('products/{product}', [ProductController::class, 'show']);
        });

        Route::middleware('module.enabled:inventory')->group(function (): void {
            Route::get('inventory/balances', [InventoryController::class, 'index']);
        });

        Route::middleware('module.enabled:replenishment')->group(function (): void {
            Route::get('replenishment/proposals', [ReplenishmentProposalController::class, 'index']);
            Route::get('replenishment/proposals/{proposal}', [ReplenishmentProposalController::class, 'show']);
        });

        Route::middleware('module.enabled:procurement')->group(function (): void {
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
            Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        });
    });
});

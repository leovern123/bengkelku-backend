<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\MechanicController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\OrderDetailController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAll']);

    // Admin dan Kasir
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('vehicles', VehicleController::class);

    Route::get('items', [ItemController::class, 'index']);
    Route::get('items/{item}', [ItemController::class, 'show']);

    // Read-only untuk kasir
    Route::get('mechanics', [MechanicController::class, 'index']);
    Route::get('item-categories', [ItemCategoryController::class, 'index']);

    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{id}/process', [OrderController::class, 'process']);
    Route::post('orders/{id}/complete', [OrderController::class, 'complete']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

    Route::apiResource('payments', PaymentController::class);

    Route::post('order-details', [OrderDetailController::class, 'store']);
    Route::put('order-details/{id}', [OrderDetailController::class, 'update']);
    Route::delete('order-details/{id}', [OrderDetailController::class, 'destroy']);

    // Admin saja
    Route::middleware('role:admin')->group(function () {
        Route::post('mechanics', [MechanicController::class, 'store']);
        Route::put('mechanics/{mechanic}', [MechanicController::class, 'update']);
        Route::patch('mechanics/{mechanic}', [MechanicController::class, 'update']);
        Route::delete('mechanics/{mechanic}', [MechanicController::class, 'destroy']);

        Route::apiResource('suppliers', SupplierController::class);

        Route::post('item-categories', [ItemCategoryController::class, 'store']);
        Route::put('item-categories/{itemCategory}', [ItemCategoryController::class, 'update']);
        Route::patch('item-categories/{itemCategory}', [ItemCategoryController::class, 'update']);
        Route::delete('item-categories/{itemCategory}', [ItemCategoryController::class, 'destroy']);

        Route::post('items', [ItemController::class, 'store']);
        Route::put('items/{item}', [ItemController::class, 'update']);
        Route::patch('items/{item}', [ItemController::class, 'update']);
        Route::delete('items/{item}', [ItemController::class, 'destroy']);

        Route::apiResource('expenses', ExpenseController::class);

        Route::get('reports/summary', [ReportController::class, 'summary']);
        Route::get('reports/transactions', [ReportController::class, 'transactions']);
        Route::get('reports/income', [ReportController::class, 'income']);
        Route::get('reports/expenses', [ReportController::class, 'expenses']);
        Route::get('reports/profit', [ReportController::class, 'profit']);
        Route::get('reports/chart', [ReportController::class, 'chart']);
        Route::get('reports/stock', [ReportController::class, 'stock']);
        Route::get('reports/favorites', [ReportController::class, 'favorites']);
    });
});
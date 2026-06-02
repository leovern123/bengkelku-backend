<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\MechanicController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SupplierController;

Route::apiResource('customers', CustomerController::class);
Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('mechanics', MechanicController::class);
Route::apiResource('item-categories', ItemCategoryController::class);
Route::apiResource('items', ItemController::class);

Route::apiResource('orders', OrderController::class);
Route::post('orders/{id}/complete', [OrderController::class, 'complete']);
Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

Route::apiResource('payments', PaymentController::class);
Route::apiResource('expenses', ExpenseController::class);

Route::get('reports/summary', [ReportController::class, 'summary']);
Route::get('reports/income', [ReportController::class, 'income']);
Route::get('reports/expenses', [ReportController::class, 'expenses']);
Route::get('reports/profit', [ReportController::class, 'profit']);
Route::get('reports/stock', [ReportController::class, 'stock']);

Route::apiResource('suppliers', SupplierController::class);
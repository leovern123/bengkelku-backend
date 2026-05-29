<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\VehicleController;

Route::apiResource('customers', CustomerController::class);
Route::apiResource('vehicles', VehicleController::class);
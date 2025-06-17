<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\DasPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('das-payments', DasPaymentController::class);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('invoices', InvoiceController::class);
// });

// Route::get('/users', [UserController::class, 'index']);
// Route::apiResource('invoices', InvoiceController::class);
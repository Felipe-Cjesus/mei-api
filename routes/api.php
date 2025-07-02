<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\DasPaymentController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EnterpriseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/users'   , [UserController::class, 'index']);

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login'   , [AuthController::class, 'login']);
Route::middleware('throttle:10,1')->post('/login', [AuthController::class, 'login']);
Route::middleware('throttle:5,1')->post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::put('/alerts/{id}/read', [AlertController::class, 'markAsRead']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('das-payments', DasPaymentController::class);
    Route::apiResource('enterprises', EnterpriseController::class);
});

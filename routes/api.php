<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransactionController;

Route::prefix('v1')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::post('/transfers', [TransferController::class, 'store']);
    Route::get('/accounts/{accountId}/transactions', [TransactionController::class, 'index']);
});

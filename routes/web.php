<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{purchaseRequest}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{purchaseRequest}/submit', [RequestController::class, 'submit'])->name('requests.submit');
    Route::post('/requests/{purchaseRequest}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
    Route::post('/requests/{purchaseRequest}/decide', [RequestController::class, 'decide'])->name('requests.decide');
});

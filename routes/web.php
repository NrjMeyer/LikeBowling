<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanceController;

Route::get('/', [LanceController::class, 'show'])->name('home');

Route::post('/store', [LanceController::class, 'store'])->name('store');
Route::post('/reset', [LanceController::class, 'reset'])->name('reset');

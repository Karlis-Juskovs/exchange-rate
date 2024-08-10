<?php

use App\Http\Controllers\ExchangeRateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ExchangeRateController::class, 'index'])->name('exchange_rate.index');

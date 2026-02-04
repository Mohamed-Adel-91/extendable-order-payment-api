<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\KashierCallbackController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment/callback', [KashierCallbackController::class, 'handle'])->name('payment.kashier.callback');

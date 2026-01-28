<?php

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

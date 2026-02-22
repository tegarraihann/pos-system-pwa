<?php

use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OfflineOrderSyncController;
use App\Http\Controllers\QzTraySigningController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/api/offline/sync-order', [OfflineOrderSyncController::class, 'store'])
    ->middleware('auth')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::middleware('auth')->group(function (): void {
    Route::get('/api/qz-tray/certificate', [QzTraySigningController::class, 'certificate']);
    Route::post('/api/qz-tray/sign', [QzTraySigningController::class, 'sign']);
});

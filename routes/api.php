<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\PaypalWebhookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/paypal', PaypalWebhookController::class)->name('webhooks.paypal');

// 同步相关路由（不需要 token 鉴权，使用 API Key）
Route::prefix('sync')->group(function () {
    Route::post('/receive-batch', [SyncController::class, 'receiveBatch'])->name('sync.receive-batch');
    Route::post('/trigger', [SyncController::class, 'triggerSync'])->name('sync.trigger');
    Route::get('/status', [SyncController::class, 'status'])->name('sync.status');
});

// 需要token鉴权的API路由
Route::middleware([ForceJsonResponse::class, AuthenticateApiToken::class])->group(function () {
    Route::post('/articles/add', [ArticleController::class, 'store']);
    Route::post('/products/add', [ProductController::class, 'store']);
});

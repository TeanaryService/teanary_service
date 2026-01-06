<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/paypal', \App\Http\Controllers\Api\PaypalWebhookController::class)->name('webhooks.paypal');

// 同步相关路由（不需要 token 鉴权，使用 API Key）
Route::prefix('sync')->group(function () {
    Route::post('/receive', [\App\Http\Controllers\Api\SyncController::class, 'receive'])->name('sync.receive');
    Route::get('/status', [\App\Http\Controllers\Api\SyncController::class, 'status'])->name('sync.status');
    Route::get('/download-file/{mediaId}', [\App\Http\Controllers\Api\SyncController::class, 'downloadFile'])->name('sync.download-file');
    Route::get('/download-file/{mediaId}/conversion/{conversion}', [\App\Http\Controllers\Api\SyncController::class, 'downloadFile'])->name('sync.download-file-conversion');
});

// 需要token鉴权的API路由
Route::middleware([ForceJsonResponse::class, AuthenticateApiToken::class])->group(function () {
    Route::post('/articles/add', [ArticleController::class, 'store']);
    Route::post('/products/add', [ProductController::class, 'store']);
});

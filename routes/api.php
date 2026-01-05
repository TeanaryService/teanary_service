<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/paypal', \App\Http\Controllers\Api\PaypalWebhookController::class)->name('webhooks.paypal');

// 需要token鉴权的API路由
Route::middleware([AuthenticateApiToken::class])->group(function () {
    Route::post('/articles/add', [ArticleController::class, 'store']);
    Route::post('/products/add', [ProductController::class, 'store']);
});

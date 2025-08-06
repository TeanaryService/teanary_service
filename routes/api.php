<?php

use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/paypal', \App\Http\Controllers\Api\PaypalWebhookController::class)->name('webhooks.paypal');

Route::post('/articles/add', [ArticleController::class, 'store']);
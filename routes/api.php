<?php

use Illuminate\Support\Facades\Route;

Route::post('webhooks/paypal', \App\Http\Controllers\Api\PaypalWebhookController::class)->name('webhooks.paypal');
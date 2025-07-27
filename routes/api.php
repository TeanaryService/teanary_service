<?php

use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/sync/pull', [SyncController::class, 'pull']);

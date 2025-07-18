<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Services\LocaleCurrencyService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        // $middleware->append(SetLocaleAndCurrency::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
    })->create();

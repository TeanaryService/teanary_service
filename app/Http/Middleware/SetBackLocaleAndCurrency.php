<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use App\Services\LocaleCurrencyService;

class SetBackLocaleAndCurrency
{
    public function handle($request, Closure $next)
    {
        $service = new LocaleCurrencyService();

        $locale = $service->resolveLocale(session('lang'));
        
        app()->setLocale($locale);

        // ------------------------------
        // 设置货币
        // ------------------------------
        $currencyCode = $request->query('currency')
            ?? Session::get('currency');

        $currency = $service->getCurrencyByCode($currencyCode);
        Session::put('currency', $currency->code);

        return $next($request);
    }
}
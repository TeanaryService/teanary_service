<?php

namespace App\Http\Middleware;

use App\Services\LocaleCurrencyService;
use Closure;
use Illuminate\Support\Facades\Session;

class SetBackLocaleAndCurrency
{
    public function handle($request, Closure $next)
    {
        $service = new LocaleCurrencyService;

        $locale = $service->resolveLocale(session('lang'));

        app()->setLocale($locale);

        // ------------------------------
        // 设置货币
        // ------------------------------
        $currencyCode = $request->query('currency')
            ?? Session::get('currency')
            ?? $service->getDefaultCurrencyCode();

        $currency = $service->getCurrencyByCode($currencyCode);
        if ($currency) {
            Session::put('currency', $currency->code);
        } else {
            // 如果表不存在或货币不存在，使用默认值
            Session::put('currency', $currencyCode ?: 'CNY');
        }

        return $next($request);
    }
}

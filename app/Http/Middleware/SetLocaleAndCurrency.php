<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Services\LocaleCurrencyService;

class SetLocaleAndCurrency
{
    public function handle($request, Closure $next)
    {
        $service = new LocaleCurrencyService();

        $locale = $service->resolveLocale($request->segment(1));

        if ($locale !== $request->segment(1)) {
            return redirect($locale);
        }
        
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
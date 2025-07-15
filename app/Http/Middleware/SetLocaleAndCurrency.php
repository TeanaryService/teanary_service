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

        // ------------------------------
        // 设置语言
        // ------------------------------
        $langCode = $request->input('lang') 
            ?? Session::get('lang');
            
        $language = $service->getLanguageByCode($langCode);
        App::setLocale($language->code);
        Session::put('lang', $language->code);

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
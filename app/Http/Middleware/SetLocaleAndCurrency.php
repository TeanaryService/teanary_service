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
        $locale = $request->segment(1); // 取 URI 第一段

        $supported = $service->getLanguages()->pluck('code')->toArray();

        if (!in_array($locale, $supported)) {
            $locale = Session::get('lang') ?? $service->getDefaultLanguageCode();
        }
        
        Session::put('lang', $locale);
        App::setLocale($locale);

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

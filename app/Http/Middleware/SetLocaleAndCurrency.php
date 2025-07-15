<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Language;
use App\Models\Currency;

class SetLocaleAndCurrency
{
    public function handle($request, Closure $next)
    {
        // ------------------------------
        // 设置语言
        // ------------------------------
        $langCode = $request->input('lang') 
            ?? Session::get('lang') 
            ?? 'en';

        $language = Language::where('code', $langCode)->first();

        if ($language) {
            App::setLocale($language->code);
            Session::put('lang', $language->code);
        } else {
            App::setLocale('en');
            Session::put('lang', 'en');
        }

        // ------------------------------
        // 设置货币
        // ------------------------------
        $currencyCode = $request->query('currency') 
            ?? Session::get('currency') 
            ?? 'USD';

        $currency = Currency::where('code', $currencyCode)->first();

        if ($currency) {
            Session::put('currency', $currency->code);
        } else {
            Session::put('currency', 'USD');
        }

        return $next($request);
    }
}
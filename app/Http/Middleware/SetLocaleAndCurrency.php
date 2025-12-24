<?php

namespace App\Http\Middleware;

use Closure;
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
        
        session(['lang' => $locale]);
        app()->setLocale($locale);

        // ------------------------------
        // 设置货币
        // ------------------------------
        $currencyCode = Session::get('currency', $service->getDefaultCurrencyCode());

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
<?php

namespace App\Http\Controllers;

use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;

class LanguageCurrencySwitcherController extends Controller
{
    public function update(Request $request)
    {
        $service = new LocaleCurrencyService;

        if ($request->filled('lang')) {
            $language = $service->getLanguageByCode($request->input('lang'));
            session(['lang' => $language->code]);
        }

        if ($request->filled('currency')) {
            $currency = $service->getCurrencyByCode($request->input('currency'));
            session(['currency' => $currency->code]);
        }

        // 简单刷新上一个页面
        return redirect()->back();
    }
}

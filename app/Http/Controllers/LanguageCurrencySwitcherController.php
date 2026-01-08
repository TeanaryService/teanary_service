<?php

namespace App\Http\Controllers;

use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;

class LanguageCurrencySwitcherController extends Controller
{
    public function __construct(
        protected LocaleCurrencyService $localeCurrencyService
    ) {}

    public function update(Request $request)
    {
        if ($request->filled('lang')) {
            $language = $this->localeCurrencyService->getLanguageByCode($request->input('lang'));
            if ($language) {
                $request->session()->put('lang', $language->code);
            } else {
                // 如果找不到语言，使用默认语言
                $request->session()->put('lang', $this->localeCurrencyService->getDefaultLanguageCode());
            }
        }

        if ($request->filled('currency')) {
            $currency = $this->localeCurrencyService->getCurrencyByCode($request->input('currency'));
            if ($currency) {
                $request->session()->put('currency', $currency->code);
            } else {
                // 如果找不到货币，使用默认货币
                $request->session()->put('currency', $this->localeCurrencyService->getDefaultCurrencyCode());
            }
        }

        // 简单刷新上一个页面
        return redirect()->back();
    }
}

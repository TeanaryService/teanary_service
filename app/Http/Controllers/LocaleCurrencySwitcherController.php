<?php

namespace App\Http\Controllers;

use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;

class LocaleCurrencySwitcherController extends Controller
{
    public function update(Request $request)
    {
        $service = new LocaleCurrencyService();

        if ($request->filled('lang')) {
            $language = $service->getLanguageByCode($request->input('lang'));
            session(['lang' => $language->code]);
        }

        if ($request->filled('currency')) {
            $currency = $service->getCurrencyByCode($request->input('currency'));
            session(['currency' => $currency->code]);
        }

        $params = [
            'lang' => session('lang'),
            'currency' => session('currency'),
        ];

        $url = url()->previous();
        $parsed = parse_url($url);
        $base = $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '') . $parsed['path'];
        return redirect()->to($base . '?' . http_build_query($params));
    }
}

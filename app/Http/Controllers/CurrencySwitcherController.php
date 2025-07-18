<?php

namespace App\Http\Controllers;

use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;

class CurrencySwitcherController extends Controller
{
    public function update(Request $request)
    {
        $service = new LocaleCurrencyService();

        if ($request->filled('currency')) {
            $currency = $service->getCurrencyByCode($request->input('currency'));
            session(['currency' => $currency->code]);
        }

        $params = [
            'currency' => session('currency'),
        ];

        $url = url()->previous();
        $parsed = parse_url($url);
        $base = $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '') . $parsed['path'];
        return redirect()->to($base . '?' . http_build_query($params));
    }
}

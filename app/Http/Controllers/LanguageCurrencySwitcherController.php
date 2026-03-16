<?php

namespace App\Http\Controllers;

use App\Services\LocaleCurrencyService;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class LanguageCurrencySwitcherController extends Controller
{
    public function __construct(
        protected LocaleCurrencyService $localeCurrencyService,
        protected WarehouseService $warehouseService
    ) {}

    public function update(Request $request)
    {
        if ($request->filled('lang')) {
            $language = $this->localeCurrencyService->getLanguageByCode($request->input('lang'));
            if ($language) {
                $request->session()->put('lang', $language->code);
            } else {
                $request->session()->put('lang', $this->localeCurrencyService->getDefaultLanguageCode());
            }
        }

        if ($request->filled('currency')) {
            $currency = $this->localeCurrencyService->getCurrencyByCode($request->input('currency'));
            if ($currency) {
                $request->session()->put('currency', $currency->code);
            } else {
                $request->session()->put('currency', $this->localeCurrencyService->getDefaultCurrencyCode());
            }
        }

        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->input('warehouse_id');
            $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
            if ($warehouse) {
                $request->session()->put('warehouse_id', $warehouse->id);
                // 切换仓库后清空购物车，避免跨仓混单
                $request->session()->forget('cart_id');
            } else {
                $request->session()->put('warehouse_id', $this->warehouseService->getDefaultWarehouseId());
            }
        }

        return redirect()->back();
    }
}

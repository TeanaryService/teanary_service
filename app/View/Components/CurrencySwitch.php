<?php

namespace App\View\Components;

use App\Services\LocaleCurrencyService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CurrencySwitch extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $service = new LocaleCurrencyService;
        $currencies = $service->getCurrencies();

        $currencyCode = session('currency');
        $selectedCurrency = $service->getCurrencyByCode($currencyCode);

        return view('components.currency-switch', [
            'currencies' => $currencies,
            'selectedCurrency' => $selectedCurrency,
        ]);
    }
}

<?php

namespace App\Observers;

use App\Models\Currency;
use App\Services\LocaleCurrencyService;

class CurrencyObserver
{
    public function saved(Currency $currency)
    {
        (new LocaleCurrencyService)->clearCurrenciesCache();
    }

    public function deleted(Currency $currency)
    {
        (new LocaleCurrencyService)->clearCurrenciesCache();
    }
}

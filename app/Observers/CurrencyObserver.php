<?php

namespace App\Observers;

use App\Models\Currency;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Artisan;

class CurrencyObserver
{
    public function saved(Currency $currency)
    {
        (new LocaleCurrencyService)->clearCurrenciesCache();
        // Artisan::call('app:update-ecb');
    }

    public function updated(Currency $currency)
    {
        (new LocaleCurrencyService)->clearCurrenciesCache();
        Artisan::call('app:update-ecb');
    }

    public function deleted(Currency $currency)
    {
        (new LocaleCurrencyService)->clearCurrenciesCache();
    }
}

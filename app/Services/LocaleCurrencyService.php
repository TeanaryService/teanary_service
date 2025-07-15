<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Language;
use App\Models\Currency;

class LocaleCurrencyService
{
    const LANGUAGES_CACHE_KEY = 'languages.all';
    const CURRENCIES_CACHE_KEY = 'currencies.all';

    public function getLanguages()
    {
        return Cache::rememberForever(self::LANGUAGES_CACHE_KEY, function () {
            return Language::all();
        });
    }

    public function getCurrencies()
    {
        return Cache::rememberForever(self::CURRENCIES_CACHE_KEY, function () {
            return Currency::all();
        });
    }

    public function clearLanguagesCache()
    {
        Cache::forget(self::LANGUAGES_CACHE_KEY);
    }

    public function clearCurrenciesCache()
    {
        Cache::forget(self::CURRENCIES_CACHE_KEY);
    }

    public function getLanguageByCode($code)
    {
        $languages = $this->getLanguages();
        return $languages->firstWhere('code', $code)
            ?? $languages->firstWhere('default', true)
            ?? $languages->first();
    }

    public function getCurrencyByCode($code)
    {
        $currencies = $this->getCurrencies();
        return $currencies->firstWhere('code', $code)
            ?? $currencies->firstWhere('default', true)
            ?? $currencies->first();
    }
}

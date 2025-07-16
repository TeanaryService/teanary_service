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

    /**
     * 获取指定币种的汇率
     */
    public function getRate(string $code): float
    {
        $currency = $this->getCurrencyByCode($code);
        return $currency ? $currency->exchange_rate : 1.0;
    }

    /**
     * 设置指定币种的汇率
     */
    public function setRate(string $code, float $rate): bool
    {
        $currency = Currency::where('code', $code)->first();
        if ($currency) {
            $currency->exchange_rate = $rate;
            $result = $currency->save();
            $this->clearCurrenciesCache();
            return $result;
        }
        return false;
    }

    /**
     * 刷新所有币种汇率（示例，实际可对接第三方API）
     */
    public function refreshRates(array $rates): void
    {
        foreach ($rates as $code => $rate) {
            $this->setRate($code, $rate);
        }
    }

    /**
     * 计算金额的汇率转换（无符号，仅数值）
     */
    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        $fromRate = $this->getRate($fromCode);
        $toRate = $this->getRate($toCode);
        if ($fromRate == 0) {
            return 0;
        }
        return $amount * ($toRate / $fromRate);
    }

    /**
     * 计算金额的汇率转换（带目标币种符号，格式化字符串）
     */
    public function convertWithSymbol(float $amount, string $fromCode, string $toCode, int $decimals = 2): string
    {
        $converted = $this->convert($amount, $fromCode, $toCode);
        $currency = $this->getCurrencyByCode($toCode);
        $symbol = $currency ? $currency->symbol : '';
        return $symbol . number_format($converted, $decimals, '.', '');
    }
}

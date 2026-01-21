<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Language;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LocaleCurrencyService
{
    public function getLanguages()
    {
        return Cache::rememberForever(CacheKeys::LANGUAGES_ALL, function () {
            if (! Schema::hasTable((new Language())->getTable())) {
                return collect();
            }

            return Language::all();
        });
    }

    public function getCurrencies()
    {
        return Cache::rememberForever(CacheKeys::CURRENCIES_ALL, function () {
            if (! Schema::hasTable((new Currency())->getTable())) {
                return collect();
            }

            return Currency::all();
        });
    }

    public function clearLanguagesCache()
    {
        Cache::forget(CacheKeys::LANGUAGES_ALL);
    }

    public function clearCurrenciesCache()
    {
        Cache::forget(CacheKeys::CURRENCIES_ALL);
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
     * 获取指定币种的汇率.
     */
    public function getRate(string $code): float
    {
        $currency = $this->getCurrencies()->firstWhere('code', $code);

        return $currency ? $currency->exchange_rate : 1.0;
    }

    /**
     * 设置指定币种的汇率.
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
     * 刷新所有币种汇率（示例，实际可对接第三方API）.
     */
    public function refreshRates(array $rates): void
    {
        foreach ($rates as $code => $rate) {
            $this->setRate($code, $rate);
        }
    }

    /**
     * 计算金额的汇率转换（无符号，仅数值）.
     */
    public function convert(float $amount, ?string $toCode, string $fromCode = ''): float
    {
        $fromCode = $fromCode ?: $this->getDefaultCurrencyCode();
        $toCode = $toCode ?: $this->getDefaultCurrencyCode();
        $fromRate = $this->getRate($fromCode);
        $toRate = $this->getRate($toCode);
        if ($fromRate == 0) {
            return 0;
        }

        return $amount * ($toRate / $fromRate);
    }

    /**
     * 计算金额的汇率转换（带目标币种符号，格式化字符串）.
     */
    public function convertWithSymbol(float $amount, ?string $toCode, string $fromCode = '', int $decimals = 2): string
    {
        $fromCode = $fromCode ?: $this->getDefaultCurrencyCode();
        $toCode = $toCode ?: $this->getDefaultCurrencyCode();
        $converted = $this->convert($amount, $toCode, $fromCode);
        $currency = $this->getCurrencyByCode($toCode);
        $symbol = $currency ? $currency->symbol : '';

        return $symbol.number_format($converted, $decimals, '.', '');
    }

    /**
     * 格式化金额（带货币符号，不进行汇率转换）.
     */
    public function formatWithSymbol(float $amount, ?string $code = null, int $decimals = 2): string
    {
        $code = $code ?: $this->getDefaultCurrencyCode();
        $currency = $this->getCurrencyByCode($code);
        $symbol = $currency ? $currency->symbol : '';

        return $symbol.number_format($amount, $decimals, '.', '');
    }

    /**
     * 获取默认币种 code.
     */
    public function getDefaultCurrencyCode(): string
    {
        $currencies = $this->getCurrencies();
        $default = $currencies->firstWhere('default', true);

        return $default ? $default->code : ($currencies->first()->code ?? 'CNY');
    }

    /**
     * 获取默认语言 code.
     */
    public function getDefaultLanguageCode(): string
    {
        $languages = $this->getLanguages();
        $default = $languages->firstWhere('default', true);

        return $default ? $default->code : ($languages->first()->code ?? 'en');
    }

    /**
     * 获取 [id => 当前语言name] 的语言选项数组
     */
    public function getLanguageOptions(): array
    {
        $languages = $this->getLanguages();

        return $languages->pluck('name', 'id')->toArray();
    }

    /**
     * 获取 [id => 当前语言name] 的货币选项数组
     */
    public function getCurrencyOptions(): array
    {
        $currencies = $this->getCurrencies();

        return $currencies->pluck('name', 'id')->toArray();
    }

    /**
     * 根据传入的 locale 判断并返回支持的语言 code.
     */
    public function resolveLocale(?string $locale): string
    {
        $supported = $this->getLanguages()->pluck('code')->toArray();
        if (! in_array($locale, $supported)) {
            return $this->getDefaultLanguageCode();
        }

        return $locale;
    }
}

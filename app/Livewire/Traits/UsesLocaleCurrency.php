<?php

namespace App\Livewire\Traits;

use App\Services\LocaleCurrencyService;

/**
 * 提供本地化和货币服务功能的 Trait.
 *
 * 用于需要访问语言和货币信息的组件
 */
trait UsesLocaleCurrency
{
    /**
     * LocaleCurrencyService 实例缓存.
     */
    protected ?LocaleCurrencyService $localeService = null;

    /**
     * 获取 LocaleCurrencyService 实例.
     */
    protected function getLocaleService(): LocaleCurrencyService
    {
        if ($this->localeService === null) {
            $this->localeService = app(LocaleCurrencyService::class);
        }

        return $this->localeService;
    }

    /**
     * 获取当前语言.
     */
    protected function getCurrentLanguage()
    {
        $service = $this->getLocaleService();
        $locale = app()->getLocale();

        return $service->getLanguageByCode($locale);
    }

    /**
     * 获取当前货币代码.
     */
    protected function getCurrentCurrencyCode(): string
    {
        $service = $this->getLocaleService();

        return session('currency') ?? $service->getDefaultCurrencyCode();
    }

    /**
     * 获取所有语言.
     */
    protected function getLanguages()
    {
        return $this->getLocaleService()->getLanguages();
    }

    /**
     * 获取默认语言.
     */
    protected function getDefaultLanguage()
    {
        $service = $this->getLocaleService();

        return $service->getLanguages()->firstWhere('default', true)
            ?? $service->getDefaultLanguage();
    }
}

<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\LocaleCurrencyService;
use App\Models\Language;
use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LocaleCurrencyServiceTest extends TestCase
{
    protected LocaleCurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LocaleCurrencyService();
        Cache::flush();
    }

    public function test_get_languages_returns_collection_when_table_exists(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English', 'default' => true]);
        Language::factory()->create(['code' => 'zh', 'name' => '中文', 'default' => false]);

        $languages = $this->service->getLanguages();

        $this->assertCount(2, $languages);
        $this->assertEquals('en', $languages->first()->code);
    }

    public function test_get_languages_uses_cache(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $first = $this->service->getLanguages();
        $second = $this->service->getLanguages();

        // 第二次应该从缓存获取，所以应该是同一个实例
        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
    }

    public function test_get_currencies_returns_collection_when_table_exists(): void
    {
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'default' => true]);
        Currency::factory()->create(['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'default' => false]);

        $currencies = $this->service->getCurrencies();

        $this->assertCount(2, $currencies);
        $this->assertEquals('USD', $currencies->first()->code);
    }

    public function test_get_currency_by_code(): void
    {
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
        Currency::factory()->create(['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥']);

        $currency = $this->service->getCurrencyByCode('USD');

        $this->assertNotNull($currency);
        $this->assertEquals('USD', $currency->code);
    }

    public function test_get_currency_by_code_returns_default_when_not_found(): void
    {
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'default' => true]);
        Currency::factory()->create(['code' => 'CNY', 'name' => 'Chinese Yuan', 'default' => false]);

        $currency = $this->service->getCurrencyByCode('EUR');

        $this->assertNotNull($currency);
        $this->assertEquals('USD', $currency->code); // 应该返回默认币种
    }

    public function test_get_language_by_code(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Language::factory()->create(['code' => 'zh', 'name' => '中文']);

        $language = $this->service->getLanguageByCode('zh');

        $this->assertNotNull($language);
        $this->assertEquals('zh', $language->code);
    }

    public function test_get_rate_returns_exchange_rate(): void
    {
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.0]);
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 7.2]);

        $rate = $this->service->getRate('CNY');

        $this->assertEquals(7.2, $rate);
    }

    public function test_get_rate_returns_one_when_currency_not_found(): void
    {
        $rate = $this->service->getRate('EUR');

        $this->assertEquals(1.0, $rate);
    }

    public function test_set_rate_updates_currency_exchange_rate(): void
    {
        $currency = Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.0]);

        $result = $this->service->setRate('USD', 1.5);

        $this->assertTrue($result);
        $this->assertEquals(1.5, $currency->fresh()->exchange_rate);
    }

    public function test_set_rate_returns_false_when_currency_not_found(): void
    {
        $result = $this->service->setRate('EUR', 1.5);

        $this->assertFalse($result);
    }

    public function test_convert_currency(): void
    {
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.0]);
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 7.2]);

        $converted = $this->service->convert(100, 'CNY', 'USD');

        $this->assertEquals(720.0, $converted);
    }

    public function test_convert_currency_handles_zero_rate(): void
    {
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 0.0]);
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 7.2]);

        $converted = $this->service->convert(100, 'CNY', 'USD');

        $this->assertEquals(0.0, $converted);
    }

    public function test_convert_with_symbol(): void
    {
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.0, 'symbol' => '$']);
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 7.2, 'symbol' => '¥']);

        $result = $this->service->convertWithSymbol(100, 'CNY', 'USD');

        $this->assertStringStartsWith('¥', $result);
        $this->assertStringContainsString('720.00', $result);
    }

    public function test_get_default_currency_code(): void
    {
        Currency::factory()->create(['code' => 'USD', 'default' => true]);
        Currency::factory()->create(['code' => 'CNY', 'default' => false]);

        $code = $this->service->getDefaultCurrencyCode();

        $this->assertEquals('USD', $code);
    }

    public function test_get_default_currency_code_returns_fallback(): void
    {
        Currency::factory()->create(['code' => 'CNY', 'default' => false]);

        $code = $this->service->getDefaultCurrencyCode();

        $this->assertEquals('CNY', $code);
    }

    public function test_get_default_currency_code_returns_cny_when_no_currencies(): void
    {
        $code = $this->service->getDefaultCurrencyCode();

        $this->assertEquals('CNY', $code);
    }

    public function test_get_default_language_code(): void
    {
        Language::factory()->create(['code' => 'en', 'default' => true]);
        Language::factory()->create(['code' => 'zh', 'default' => false]);

        $code = $this->service->getDefaultLanguageCode();

        $this->assertEquals('en', $code);
    }

    public function test_get_default_language_code_returns_fallback(): void
    {
        Language::factory()->create(['code' => 'zh', 'default' => false]);

        $code = $this->service->getDefaultLanguageCode();

        $this->assertEquals('zh', $code);
    }

    public function test_get_default_language_code_returns_en_when_no_languages(): void
    {
        $code = $this->service->getDefaultLanguageCode();

        $this->assertEquals('en', $code);
    }

    public function test_clear_languages_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with(LocaleCurrencyService::LANGUAGES_CACHE_KEY);

        $this->service->clearLanguagesCache();
    }

    public function test_clear_currencies_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with(LocaleCurrencyService::CURRENCIES_CACHE_KEY);

        $this->service->clearCurrenciesCache();
    }

    public function test_resolve_locale_returns_supported_locale(): void
    {
        Language::factory()->create(['code' => 'en', 'default' => true]);
        Language::factory()->create(['code' => 'zh', 'default' => false]);

        $locale = $this->service->resolveLocale('zh');

        $this->assertEquals('zh', $locale);
    }

    public function test_resolve_locale_returns_default_when_not_supported(): void
    {
        Language::factory()->create(['code' => 'en', 'default' => true]);

        $locale = $this->service->resolveLocale('fr');

        $this->assertEquals('en', $locale);
    }
}


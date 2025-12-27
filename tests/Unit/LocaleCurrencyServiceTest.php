<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\Language;
use App\Services\LocaleCurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LocaleCurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LocaleCurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LocaleCurrencyService;
    }

    public function test_get_languages()
    {
        $language = Language::factory()->create();

        $result = $this->service->getLanguages();

        $this->assertCount(1, $result);
        $this->assertTrue(Cache::has(LocaleCurrencyService::LANGUAGES_CACHE_KEY));
    }

    public function test_get_currencies()
    {
        $currency = Currency::factory()->create();

        $result = $this->service->getCurrencies();

        $this->assertCount(1, $result);
        $this->assertTrue(Cache::has(LocaleCurrencyService::CURRENCIES_CACHE_KEY));
    }

    public function test_clear_languages_cache()
    {
        Cache::put(LocaleCurrencyService::LANGUAGES_CACHE_KEY, collect([]));
        $this->assertTrue(Cache::has(LocaleCurrencyService::LANGUAGES_CACHE_KEY));

        $this->service->clearLanguagesCache();

        $this->assertFalse(Cache::has(LocaleCurrencyService::LANGUAGES_CACHE_KEY));
    }

    public function test_clear_currencies_cache()
    {
        Cache::put(LocaleCurrencyService::CURRENCIES_CACHE_KEY, collect([]));
        $this->assertTrue(Cache::has(LocaleCurrencyService::CURRENCIES_CACHE_KEY));

        $this->service->clearCurrenciesCache();

        $this->assertFalse(Cache::has(LocaleCurrencyService::CURRENCIES_CACHE_KEY));
    }

    public function test_get_language_by_code()
    {
        $language = Language::factory()->create(['code' => 'en', 'default' => true]);

        $result = $this->service->getLanguageByCode('en');

        $this->assertNotNull($result);
        $this->assertEquals('en', $result->code);
    }

    public function test_get_language_by_code_returns_default_when_not_found()
    {
        $defaultLanguage = Language::factory()->create(['code' => 'en', 'default' => true]);
        Language::factory()->create(['code' => 'zh', 'default' => false]);

        $result = $this->service->getLanguageByCode('fr');

        $this->assertNotNull($result);
        $this->assertEquals('en', $result->code);
    }

    public function test_get_currency_by_code()
    {
        $currency = Currency::factory()->create(['code' => 'USD', 'default' => true]);

        $result = $this->service->getCurrencyByCode('USD');

        $this->assertNotNull($result);
        $this->assertEquals('USD', $result->code);
    }

    public function test_get_rate()
    {
        $currency = Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 7.2]);

        $result = $this->service->getRate('USD');

        $this->assertEquals(7.2, $result);
    }

    public function test_get_rate_returns_one_when_currency_not_found()
    {
        $result = $this->service->getRate('INVALID');

        $this->assertEquals(1.0, $result);
    }

    public function test_set_rate()
    {
        $currency = Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 7.0]);

        $result = $this->service->setRate('USD', 7.5);

        $this->assertTrue($result);
        $this->assertEquals(7.5, $currency->fresh()->exchange_rate);
        $this->assertFalse(Cache::has(LocaleCurrencyService::CURRENCIES_CACHE_KEY));
    }

    public function test_set_rate_returns_false_when_currency_not_found()
    {
        $result = $this->service->setRate('INVALID', 7.5);

        $this->assertFalse($result);
    }

    public function test_convert()
    {
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 1.0]);
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 7.0]);

        $result = $this->service->convert(100, 'USD', 'CNY');

        $this->assertEquals(700, $result);
    }

    public function test_convert_with_symbol()
    {
        Currency::factory()->create(['code' => 'CNY', 'exchange_rate' => 1.0, 'symbol' => '¥']);
        Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 7.0, 'symbol' => '$']);

        $result = $this->service->convertWithSymbol(100, 'USD', 'CNY');

        $this->assertStringStartsWith('$', $result);
        $this->assertStringContainsString('700.00', $result);
    }

    public function test_format_with_symbol()
    {
        Currency::factory()->create(['code' => 'USD', 'symbol' => '$']);

        $result = $this->service->formatWithSymbol(100.50, 'USD');

        $this->assertStringStartsWith('$', $result);
        $this->assertStringContainsString('100.50', $result);
    }

    public function test_get_default_currency_code()
    {
        Currency::factory()->create(['code' => 'CNY', 'default' => true]);
        Currency::factory()->create(['code' => 'USD', 'default' => false]);

        $result = $this->service->getDefaultCurrencyCode();

        $this->assertEquals('CNY', $result);
    }

    public function test_get_default_language_code()
    {
        Language::factory()->create(['code' => 'en', 'default' => true]);
        Language::factory()->create(['code' => 'zh', 'default' => false]);

        $result = $this->service->getDefaultLanguageCode();

        $this->assertEquals('en', $result);
    }

    public function test_resolve_locale()
    {
        Language::factory()->create(['code' => 'en']);
        Language::factory()->create(['code' => 'zh']);

        $result = $this->service->resolveLocale('en');

        $this->assertEquals('en', $result);
    }

    public function test_resolve_locale_returns_default_when_not_supported()
    {
        Language::factory()->create(['code' => 'en', 'default' => true]);

        $result = $this->service->resolveLocale('fr');

        $this->assertEquals('en', $result);
    }
}

<?php

namespace Tests\Unit;

use App\Services\LocaleCurrencyService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\Store as CacheStoreContract;
use Illuminate\Session\Store as SessionStore; // Use the concrete class
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session as SessionFacade;
use Mockery;
use Tests\TestCase;

class LanguageCurrencySwitcherControllerTest extends TestCase
{
    use RefreshDatabase;

    protected LocaleCurrencyService $localeCurrencyService;
    protected Mockery\MockInterface $sessionStore;
    protected Mockery\MockInterface $cacheStore;
    protected Mockery\MockInterface $concreteCacheStore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeCurrencyService = $this->mock(LocaleCurrencyService::class);

        // Mock the static Cache facade methods
        Cache::shouldReceive('rememberForever')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function ($key, $callback) {
                return $callback(); // Just execute the callback directly
            });
        Cache::shouldReceive('forget')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        // Mock the cache store returned by Cache::store()
        $this->concreteCacheStore = Mockery::mock(CacheStoreContract::class); // Mock the concrete cache store
        $this->concreteCacheStore->shouldReceive('setConnection')
            ->zeroOrMoreTimes()
            ->andReturnSelf(); // Allow setConnection to be called and return itself

        $this->cacheStore = Mockery::mock(CacheRepository::class);
        $this->cacheStore->shouldReceive('getStore') // Expect getStore() to be called
            ->zeroOrMoreTimes()
            ->andReturn($this->concreteCacheStore); // Return the concrete cache store mock

        Cache::shouldReceive('store')
            ->zeroOrMoreTimes()
            ->andReturn($this->cacheStore);

        $this->cacheStore->shouldReceive('rememberForever')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function ($key, $callback) {
                return $callback(); // Just execute the callback directly
            });
        $this->cacheStore->shouldReceive('forget')
            ->zeroOrMoreTimes()
            ->andReturn(null);
        $this->cacheStore->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn(null);
        $this->cacheStore->shouldReceive('put')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        // Mock the session store
        $this->sessionStore = Mockery::mock(SessionStore::class); // Mock the concrete class
        $this->app->instance('session.store', $this->sessionStore);

        // Default session expectations for tests that don't explicitly mock them
        $this->sessionStore->shouldReceive('get')
            ->with('currency', Mockery::any())
            ->andReturn('USD')
            ->byDefault();
        $this->sessionStore->shouldReceive('put')
            ->with('lang', Mockery::any())
            ->andReturn('en')
            ->byDefault();

        // Mock the previous URL in the session for redirect()->back()
        $this->sessionStore->shouldReceive('previousUrl')
            ->zeroOrMoreTimes()
            ->andReturn('http://localhost:8013/en/previous-page')
            ->byDefault();
        $this->sessionStore->shouldReceive('get')
            ->with('_previous.url', Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn('http://localhost:8013/en/previous-page')
            ->byDefault();
    }

    public function test_update_switches_language_and_currency_and_redirects_back()
    {
        // Arrange
        $language = \App\Models\Language::factory()->create(['code' => 'fr', 'default' => false]);
        $currency = \App\Models\Currency::factory()->create(['code' => 'EUR', 'default' => false]);
        \App\Models\Language::factory()->create(['code' => 'en', 'default' => true]); // Default language
        \App\Models\Currency::factory()->create(['code' => 'CNY', 'default' => true]); // Default currency

        $request = Request::create('/en/some-page', 'POST', [
            'lang' => 'fr',
            'currency' => 'EUR',
        ]);
        $request->headers->set('Referer', 'http://localhost:8013/en/previous-page');
        $request->setLaravelSession($this->sessionStore);
        $this->app->instance('request', $request);

        $this->localeCurrencyService->shouldReceive('getLanguageByCode')
            ->once()
            ->with('fr')
            ->andReturn($language);
        $this->localeCurrencyService->shouldReceive('getCurrencyByCode')
            ->once()
            ->with('EUR')
            ->andReturn($currency);
        
        $this->sessionStore->shouldReceive('put')
            ->with('lang', 'fr')
            ->once();
        $this->sessionStore->shouldReceive('put')
            ->with('currency', 'EUR')
            ->once();

        $controller = new \App\Http\Controllers\LanguageCurrencySwitcherController($this->localeCurrencyService);

        // Act
        $response = $controller->update($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost:8013/en/previous-page', $response->getTargetUrl());
    }

    public function test_update_uses_default_language_and_currency_if_invalid_provided()
    {
        // Arrange
        $defaultLanguage = \App\Models\Language::factory()->create(['code' => 'en', 'default' => true]);
        $defaultCurrency = \App\Models\Currency::factory()->create(['code' => 'CNY', 'default' => true]);

        $request = Request::create('/en/some-page', 'POST', [
            'lang' => 'invalid-lang',
            'currency' => 'invalid-currency',
        ]);
        $request->headers->set('Referer', 'http://localhost:8013/en/previous-page');
        $request->setLaravelSession($this->sessionStore);
        $this->app->instance('request', $request);

        $this->localeCurrencyService->shouldReceive('getLanguageByCode')
            ->once()
            ->with('invalid-lang')
            ->andReturn(null); // Invalid language
        $this->localeCurrencyService->shouldReceive('getCurrencyByCode')
            ->once()
            ->with('invalid-currency')
            ->andReturn(null); // Invalid currency
        $this->localeCurrencyService->shouldReceive('getDefaultLanguageCode')
            ->once()
            ->andReturn('en');
        $this->localeCurrencyService->shouldReceive('getDefaultCurrencyCode')
            ->once()
            ->andReturn('CNY');
        
        $this->sessionStore->shouldReceive('put')
            ->with('lang', 'en')
            ->once();
        $this->sessionStore->shouldReceive('put')
            ->with('currency', 'CNY')
            ->once();

        $controller = new \App\Http\Controllers\LanguageCurrencySwitcherController($this->localeCurrencyService);

        // Act
        $response = $controller->update($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost:8013/en/previous-page', $response->getTargetUrl());
    }

    public function test_update_only_switches_language()
    {
        // Arrange
        $language = \App\Models\Language::factory()->create(['code' => 'fr', 'default' => false]);
        \App\Models\Language::factory()->create(['code' => 'en', 'default' => true]);
        \App\Models\Currency::factory()->create(['code' => 'CNY', 'default' => true]);

        $request = Request::create('/en/some-page', 'POST', [
            'lang' => 'fr',
        ]);
        $request->headers->set('Referer', 'http://localhost:8013/en/previous-page');
        $request->setLaravelSession($this->sessionStore);
        $this->app->instance('request', $request);

        $this->localeCurrencyService->shouldReceive('getLanguageByCode')
            ->once()
            ->with('fr')
            ->andReturn($language);
        $this->localeCurrencyService->shouldNotReceive('getCurrencyByCode'); // Not called
        
        $this->sessionStore->shouldReceive('put')
            ->with('lang', 'fr')
            ->once();

        $controller = new \App\Http\Controllers\LanguageCurrencySwitcherController($this->localeCurrencyService);

        // Act
        $response = $controller->update($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost:8013/en/previous-page', $response->getTargetUrl());
    }

    public function test_update_only_switches_currency()
    {
        // Arrange
        $currency = \App\Models\Currency::factory()->create(['code' => 'EUR', 'default' => false]);
        \App\Models\Language::factory()->create(['code' => 'en', 'default' => true]);
        \App\Models\Currency::factory()->create(['code' => 'CNY', 'default' => true]);

        $request = Request::create('/en/some-page', 'POST', [
            'currency' => 'EUR',
        ]);
        $request->headers->set('Referer', 'http://localhost:8013/en/previous-page');
        $request->setLaravelSession($this->sessionStore);
        $this->app->instance('request', $request);

        $this->localeCurrencyService->shouldNotReceive('getLanguageByCode'); // Not called
        $this->localeCurrencyService->shouldReceive('getCurrencyByCode')
            ->once()
            ->with('EUR')
            ->andReturn($currency);
        
        $this->sessionStore->shouldReceive('put')
            ->with('currency', 'EUR')
            ->once();

        $controller = new \App\Http\Controllers\LanguageCurrencySwitcherController($this->localeCurrencyService);

        // Act
        $response = $controller->update($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('http://localhost:8013/en/previous-page', $response->getTargetUrl());
    }
}

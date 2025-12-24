<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use App\Http\Middleware\SetLocaleAndCurrency;
use App\Models\Language;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SetLocaleAndCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected SetLocaleAndCurrency $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetLocaleAndCurrency();
    }

    public function test_middleware_sets_locale_from_url_segment(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'default' => true]);

        $request = Request::create('/en/products', 'GET');
        $next = function ($req) {
            return response('OK');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', Session::get('lang'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_redirects_when_locale_not_supported(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English', 'default' => true]);
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'default' => true]);

        $request = Request::create('/fr/products', 'GET');
        $next = function ($req) {
            return response('OK');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_middleware_sets_currency_from_session(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
        Currency::factory()->create(['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥']);

        Session::put('currency', 'CNY');

        $request = Request::create('/en/products', 'GET');
        $next = function ($req) {
            return response('OK');
        };

        $this->middleware->handle($request, $next);

        $this->assertEquals('CNY', Session::get('currency'));
    }

    public function test_middleware_uses_default_currency_when_not_in_session(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'default' => true]);

        $request = Request::create('/en/products', 'GET');
        $next = function ($req) {
            return response('OK');
        };

        $this->middleware->handle($request, $next);

        $this->assertEquals('USD', Session::get('currency'));
    }

    public function test_middleware_handles_missing_currency_gracefully(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $request = Request::create('/en/products', 'GET');
        $next = function ($req) {
            return response('OK');
        };

        $this->middleware->handle($request, $next);

        // 应该使用默认值 CNY
        $this->assertNotNull(Session::get('currency'));
    }
}


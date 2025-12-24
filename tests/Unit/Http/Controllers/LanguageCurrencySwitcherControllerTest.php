<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use App\Http\Controllers\LanguageCurrencySwitcherController;
use App\Models\Language;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class LanguageCurrencySwitcherControllerTest extends TestCase
{
    use RefreshDatabase;

    protected LanguageCurrencySwitcherController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new LanguageCurrencySwitcherController();
    }

    public function test_update_sets_language_in_session(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Language::factory()->create(['code' => 'zh', 'name' => '中文']);

        $request = \Illuminate\Http\Request::create('/currency-switcher/update', 'POST', [
            'lang' => 'zh'
        ]);

        $response = $this->controller->update($request);

        $this->assertEquals('zh', Session::get('lang'));
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_update_sets_currency_in_session(): void
    {
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
        Currency::factory()->create(['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥']);

        $request = \Illuminate\Http\Request::create('/currency-switcher/update', 'POST', [
            'currency' => 'CNY'
        ]);

        $response = $this->controller->update($request);

        $this->assertEquals('CNY', Session::get('currency'));
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_update_sets_both_language_and_currency(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);

        $request = \Illuminate\Http\Request::create('/currency-switcher/update', 'POST', [
            'lang' => 'en',
            'currency' => 'USD'
        ]);

        $response = $this->controller->update($request);

        $this->assertEquals('en', Session::get('lang'));
        $this->assertEquals('USD', Session::get('currency'));
    }

    public function test_update_handles_missing_language_gracefully(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English', 'default' => true]);

        $request = \Illuminate\Http\Request::create('/currency-switcher/update', 'POST', [
            'lang' => 'nonexistent'
        ]);

        // 应该使用默认语言
        $response = $this->controller->update($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }
}


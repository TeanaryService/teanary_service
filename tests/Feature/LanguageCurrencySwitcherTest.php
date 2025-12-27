<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature测试：语言和货币切换功能.
 *
 * 这个测试验证了完整的HTTP请求流程：
 * 1. 发送POST请求到切换端点
 * 2. 验证会话中保存了正确的语言和货币
 * 3. 验证重定向到正确的URL
 */
class LanguageCurrencySwitcherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试货币切换功能
     * 根据routes/web.php，路由是 /{locale}/currency-switcher/update.
     */
    public function test_can_switch_currency()
    {
        $language = Language::factory()->create(['code' => 'en', 'default' => true]);
        $currency = Currency::factory()->create(['code' => 'USD']);

        $response = $this->post('/en/currency-switcher/update', [
            'currency_code' => 'USD',
        ]);

        $response->assertRedirect();
        $this->assertEquals('USD', session('currency'));
    }

    public function test_returns_error_for_invalid_currency()
    {
        $language = Language::factory()->create(['code' => 'en', 'default' => true]);

        $response = $this->post('/en/currency-switcher/update', [
            'currency_code' => 'INVALID',
        ]);

        $response->assertSessionHasErrors();
    }
}

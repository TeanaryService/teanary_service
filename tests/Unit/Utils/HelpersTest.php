<?php

namespace Tests\Unit\Utils;

use Tests\TestCase;
use App\Models\Language;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 确保路由已加载
        if (!Route::has('home')) {
            Route::get('/{locale}', function () {
                return 'home';
            })->name('home');
        }
    }

    public function test_is_image_returns_true_for_image_extensions(): void
    {
        $this->assertTrue(isImage('test.jpg'));
        $this->assertTrue(isImage('test.jpeg'));
        $this->assertTrue(isImage('test.png'));
        $this->assertTrue(isImage('test.gif'));
        $this->assertTrue(isImage('test.bmp'));
        $this->assertTrue(isImage('test.webp'));
    }

    public function test_is_image_returns_false_for_non_image_extensions(): void
    {
        $this->assertFalse(isImage('test.pdf'));
        $this->assertFalse(isImage('test.txt'));
        $this->assertFalse(isImage('test.doc'));
        $this->assertFalse(isImage('test'));
    }

    public function test_is_image_is_case_insensitive(): void
    {
        $this->assertTrue(isImage('test.JPG'));
        $this->assertTrue(isImage('test.PNG'));
        $this->assertTrue(isImage('test.Gif'));
    }

    public function test_loca_route_adds_locale_parameter(): void
    {
        app()->setLocale('zh');

        $url = locaRoute('home');

        // locaRoute 会将 locale 作为路径参数，不是查询参数
        $this->assertStringContainsString('zh', $url);
    }

    public function test_loca_route_uses_existing_locale_parameter(): void
    {
        app()->setLocale('en');

        $url = locaRoute('home', ['locale' => 'zh']);

        $this->assertStringContainsString('zh', $url);
        // 确保使用了传入的 locale 参数
        $this->assertStringNotContainsString('/en/', $url);
    }

    public function test_switch_locale_url_replaces_locale_in_path(): void
    {
        $this->get('/en/products/item-1');

        $newUrl = switch_locale_url('zh');

        $this->assertStringContainsString('zh/products/item-1', $newUrl);
    }

    public function test_switch_locale_url_adds_locale_when_missing(): void
    {
        $this->get('/products/item-1');

        $newUrl = switch_locale_url('zh');

        $this->assertStringContainsString('zh/products/item-1', $newUrl);
    }
}


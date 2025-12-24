<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Console\Commands\GenerateSitemap;
use App\Models\Language;
use App\Models\Product;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GenerateSitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 确保 public 目录存在
        if (!File::exists(public_path())) {
            File::makeDirectory(public_path(), 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // 清理生成的 sitemap.xml
        if (File::exists(public_path('sitemap.xml'))) {
            File::delete(public_path('sitemap.xml'));
        }
        parent::tearDown();
    }

    public function test_sitemap_generation_creates_file(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $this->artisan('app:sitemap')
            ->expectsOutput('Generating sitemap...')
            ->expectsOutput('✅ Sitemap generated at public/sitemap.xml')
            ->assertSuccessful();

        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_sitemap_includes_static_pages(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringContainsString('en', $content);
        $this->assertStringContainsString('en/about', $content);
        $this->assertStringContainsString('en/contact', $content);
    }

    public function test_sitemap_includes_products(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Product::factory()->create(['slug' => 'test-product']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringContainsString('product', $content);
    }

    public function test_sitemap_includes_articles(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Article::factory()->create(['slug' => 'test-article']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringContainsString('article', $content);
    }

    public function test_sitemap_includes_categories(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Category::factory()->create(['slug' => 'test-category']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringContainsString('product', $content);
    }

    public function test_sitemap_has_valid_xml_structure(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('</urlset>', $content);
    }

    public function test_sitemap_includes_multiple_languages(): void
    {
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Language::factory()->create(['code' => 'zh', 'name' => '中文']);

        $this->artisan('app:sitemap');

        $content = File::get(public_path('sitemap.xml'));

        $this->assertStringContainsString('en', $content);
        $this->assertStringContainsString('zh', $content);
    }
}


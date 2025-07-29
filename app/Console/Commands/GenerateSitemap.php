<?php

namespace App\Console\Commands;

use App\Services\LocaleCurrencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'app:sitemap';
    protected $description = 'Generate sitemap.xml for the website';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages(); // 如：['en', 'fr', 'de']

        $urls = [];

        foreach ($languages as $lang) {
            // 静态页面（可根据需要添加）
            $urls[] = url("$lang->code");

            // 模型：产品
            foreach (\App\Models\Product::all() as $product) {
                $urls[] = route('product.show', ['slug' => $product->slug, 'locale' => $lang->code], false);
            }

            // 模型：文章
            foreach (\App\Models\Article::all() as $post) {
                $urls[] = route('article.show', ['slug' => $post->slug, 'locale' => $lang->code], false);
            }
        }

        // 把所有 URL 转换为完整地址
        $urls = array_map(function ($path) {
            return url($path);
        }, $urls);

        // 生成 XML
        $xml = $this->generateXml($urls);

        // 写入到 public/sitemap.xml
        File::put(public_path('sitemap.xml'), $xml);

        $this->info('✅ Sitemap generated at public/sitemap.xml');
    }

    protected function generateXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url}</loc>\n";
            $xml .= "    <lastmod>" . now()->toAtomString() . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    }
}

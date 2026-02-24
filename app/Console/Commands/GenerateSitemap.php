<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use App\Services\LocaleCurrencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    protected $signature = 'app:sitemap';

    protected $description = 'Generate sitemap.xml (index) and per-language sitemaps in public/sitemap/';

    protected string $sitemapDir;

    public function handle()
    {
        $this->info('Generating sitemap...');

        $this->sitemapDir = public_path('sitemap');
        if (! File::isDirectory($this->sitemapDir)) {
            File::makeDirectory($this->sitemapDir, 0755, true);
        }

        $languages = app(LocaleCurrencyService::class)->getLanguages();
        if ($languages->isEmpty()) {
            $this->warn('No languages configured. Using default locale "en".');
            $languages = collect([(object) ['code' => 'en']]);
        }
        $sitemapFiles = [];

        foreach ($languages as $lang) {
            $locale = $lang->code;
            $filename = 'sitemap-'.str_replace('_', '-', $locale).'.xml';
            $filepath = $this->sitemapDir.DIRECTORY_SEPARATOR.$filename;

            $urls = $this->collectUrlsForLocale($locale);
            File::put($filepath, $this->generateUrlsetXml($urls));

            $sitemapFiles[] = [
                'loc' => URL::to('sitemap/'.$filename),
                'lastmod' => now()->toAtomString(),
            ];
        }

        // 生成 sitemap 索引，放在 public/sitemap/sitemap.xml
        $indexPath = $this->sitemapDir.DIRECTORY_SEPARATOR.'sitemap.xml';
        File::put($indexPath, $this->generateSitemapIndexXml($sitemapFiles));

        // 更新 robots.txt 中的 Sitemap 配置
        $this->updateRobotsTxt();

        $this->info('✅ Sitemap index: public/sitemap/sitemap.xml');
        $this->info('✅ Language sitemaps: '.implode(', ', array_column($sitemapFiles, 'loc')));
    }

    /**
     * 收集指定语言的所有 URL.
     */
    protected function collectUrlsForLocale(string $locale): array
    {
        $urls = [];

        // 静态页面
        $urls[] = URL::to(route('home', ['locale' => $locale], false));
        $urls[] = URL::to(route('product', ['locale' => $locale], false));
        $urls[] = URL::to(route('article.index', ['locale' => $locale], false));

        // 产品详情
        $this->addModelUrls($urls, Product::class, 'product.show', 'slug', $locale);

        // 文章
        $this->addModelUrls($urls, Article::class, 'article.show', 'slug', $locale);

        // 分类（产品列表页）
        $this->addModelUrls($urls, Category::class, 'product', 'slug', $locale);

        return $urls;
    }

    /**
     * 添加模型路由 URL（分块）.
     */
    protected function addModelUrls(array &$urls, string $modelClass, string $routeName, string $slugField, string $locale): void
    {
        $modelClass::chunk(100, function ($items) use (&$urls, $routeName, $slugField, $locale) {
            foreach ($items as $item) {
                $path = route($routeName, [
                    'slug' => $item->{$slugField},
                    'locale' => $locale,
                ], false);

                $urls[] = URL::to($path);
            }
        });
    }

    /**
     * 生成 urlset XML（单个语言的 sitemap）.
     */
    protected function generateUrlsetXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url)."</loc>\n";
            $xml .= '    <lastmod>'.now()->toAtomString()."</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'.PHP_EOL;

        return $xml;
    }

    /**
     * 生成 sitemap 索引 XML.
     */
    protected function generateSitemapIndexXml(array $sitemapFiles): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($sitemapFiles as $file) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>'.e($file['loc'])."</loc>\n";
            $xml .= '    <lastmod>'.$file['lastmod']."</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>'.PHP_EOL;

        return $xml;
    }

    /**
     * 更新 robots.txt，确保包含正确的 Sitemap 配置.
     */
    protected function updateRobotsTxt(): void
    {
        $robotsPath = public_path('robots.txt');
        $sitemapUrl = rtrim(config('app.url'), '/').'/sitemap/sitemap.xml';
        $sitemapLine = "Sitemap: {$sitemapUrl}";

        $content = File::exists($robotsPath) ? File::get($robotsPath) : "User-agent: *\nDisallow:\n";
        $content = preg_replace('/\n?Sitemap:.*$/m', '', $content);
        $content = rtrim($content)."\n\n{$sitemapLine}\n";

        File::put($robotsPath, $content);
    }
}

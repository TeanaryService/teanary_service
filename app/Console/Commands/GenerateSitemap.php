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
    protected $description = 'Generate sitemap.xml for the website';

    protected array $urls = [];

    public function handle()
    {
        $this->info('Generating sitemap...');

        $languages = app(LocaleCurrencyService::class)->getLanguages(); // 如返回对象数组：[{code: 'en'}, {code: 'fr'}]

        foreach ($languages as $lang) {
            $locale = $lang->code;

            // 添加静态页面
            $this->addUrl("{$locale}");
            $this->addUrl("{$locale}/about");
            $this->addUrl("{$locale}/contact");

            // 添加产品（分块）
            $this->addModelUrls(Product::class, 'product.show', 'slug', $locale);

            // 添加文章
            $this->addModelUrls(Article::class, 'article.show', 'slug', $locale);

            // 添加分类
            $this->addModelUrls(Category::class, 'product', 'slug', $locale);
        }

        // 写入 sitemap.xml 到 public 根目录
        File::put(public_path('sitemap.xml'), $this->generateXml());

        $this->info('✅ Sitemap generated at public/sitemap.xml');
    }

    /**
     * 添加模型路由 URL（分块）
     */
    protected function addModelUrls(string $modelClass, string $routeName, string $slugField, string $locale): void
    {
        $modelClass::chunk(100, function ($items) use ($routeName, $slugField, $locale) {
            foreach ($items as $item) {
                $path = route($routeName, [
                    'slug' => $item->{$slugField},
                    'locale' => $locale,
                ], false);

                $this->addUrl($path);
            }
        });
    }

    /**
     * 添加 URL（会自动转为完整地址）
     */
    protected function addUrl(string $path): void
    {
        $this->urls[] = URL::to($path);
    }

    /**
     * 生成 XML 内容
     */
    protected function generateXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($this->urls as $url) {
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

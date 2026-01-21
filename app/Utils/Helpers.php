<?php

use App\Services\LocaleCurrencyService;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

if (! function_exists('isImage')) {
    function isImage($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }
}

if (! function_exists('generateRandomImage')) {
    function generateRandomImage()
    {
        $disk = Storage::disk('local');
        $folder = 'demo';

        $files = $disk->files($folder);

        $images = array_filter($files, function ($file) {
            return isImage($file);
        });

        if (empty($images)) {
            return null;
        }

        return $disk->path($images[array_rand($images)]);
    }
}

if (! function_exists('getCurrentLocale')) {
    /**
     * 获取当前 locale，优先从请求中获取
     */
    function getCurrentLocale(): string
    {
        // 优先从请求中获取 locale（更可靠），然后从 session，最后从 app locale
        $locale = request()->segment(1) 
            ?? session('lang') 
            ?? app()->getLocale();
        
        // 如果 locale 不在支持的语言列表中，使用默认语言
        if ($locale) {
            $service = app(\App\Services\LocaleCurrencyService::class);
            $supportedLocales = $service->getLanguages()->pluck('code')->toArray();
            if (!empty($supportedLocales) && !in_array($locale, $supportedLocales)) {
                $locale = $service->getDefaultLanguageCode();
            }
        }
        
        // 如果还是没有 locale，使用默认值
        if (!$locale) {
            $service = app(\App\Services\LocaleCurrencyService::class);
            $locale = $service->getDefaultLanguageCode();
        }
        
        return $locale;
    }
}

if (! function_exists('locaRoute')) {
    /**
     * Generate a route URL with current locale prefix.
     * 
     * This function automatically detects routes that require locale prefix
     * and uses Laravel's route() function to generate URLs.
     */
    function locaRoute(string $name, array $parameters = [], bool $absolute = true): string
    {
        // 获取当前语言
        $locale = getCurrentLocale();

        // 如果已有 locale 参数（优先）就使用它，否则加上当前 locale
        if (! isset($parameters['locale'])) {
            $parameters = ['locale' => $locale] + $parameters;
        }

        // 处理对象参数（如 Order 模型对象）
        foreach ($parameters as $key => $value) {
            if (is_object($value) && method_exists($value, 'getRouteKey')) {
                $parameters[$key] = $value->getRouteKey();
            } elseif (is_object($value) && isset($value->id)) {
                $parameters[$key] = $value->id;
            }
        }

        // 调用 Laravel 原生 route() 函数
        // Laravel 会自动处理所有路由，包括那些在 {locale} 前缀下的路由
        // 所有路由都在 Route::prefix('{locale}') 下定义，所以会自动包含 locale 参数
        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('switch_locale_url')) {
    function switch_locale_url(string $newLang): string
    {
        $uri = request()->path(); // e.g. 'en/products/item-1'
        $segments = explode('/', $uri);

        $service = new LocaleCurrencyService;

        $supportedLocales = $service->getLanguages()->pluck('code')->toArray();
        if (in_array($segments[0], $supportedLocales)) {
            $segments[0] = $newLang;
        } else {
            array_unshift($segments, $newLang);
        }

        return url(implode('/', $segments));
    }
}

if (! function_exists('reusableRichEditor')) {
    function reusableRichEditor(string $field, string $default, $label, $langId): RichEditor
    {
        $path = 'editor-uploads/'.date('Y-m');

        return RichEditor::make($field)
            ->fileAttachmentsDisk('public')
            ->label($label)
            ->default($default)
            ->fileAttachmentsDirectory($path)
            ->saveUploadedFileAttachmentsUsing(function (TemporaryUploadedFile $file) use ($path) {
                $path = $file->storePublicly($path, 'public');

                \App\Models\EditorUpload::create([
                    'path' => $path,
                    'used' => false,
                ]);

                return $path;
            })
            ->extraAttributes(['data-lang-id' => $langId]);
    }
}

if (! function_exists('getProductDisplayData')) {
    /**
     * 获取产品显示数据（用于 product-item 组件）
     */
    function getProductDisplayData($product): array
    {
        $locale = session('lang');
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
        $currencyService = app(\App\Services\LocaleCurrencyService::class);
        $currencyCode = session('currency');
        
        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
        $name = $translation && $translation->name
            ? $translation->name
            : $product->productTranslations->first()->name ?? $product->slug;
        
        $variants = $product->productVariants;
        
        // 获取产品的所有图片
        $images = $product->getMedia('images');
        $firstImage = $images->first()?->getUrl('thumb') ?? $product->getFirstMediaUrl('images', 'thumb');
        $secondImage = $images->count() > 1 ? $images->get(1)?->getUrl('thumb') : null;
        
        $prices = $variants->pluck('price')->filter()->sort()->values();
        if ($prices->count() === 1) {
            $converted = $currencyService->convertWithSymbol(amount: $prices->first(), toCode: $currencyCode);
            $priceText = $converted;
        } elseif ($prices->count() > 1) {
            $min = $currencyService->convertWithSymbol(amount: $prices->first(), toCode: $currencyCode);
            $max = $currencyService->convertWithSymbol(amount: $prices->last(), toCode: $currencyCode);
            $priceText = $min . ' - ' . $max;
        } else {
            $priceText = '';
        }
        
        return [
            'name' => $name,
            'firstImage' => $firstImage,
            'secondImage' => $secondImage,
            'priceText' => $priceText,
            'images' => $images,
        ];
    }
}

if (! function_exists('getArticleDisplayData')) {
    /**
     * 获取文章显示数据（用于 article-item 组件）
     */
    function getArticleDisplayData($article): array
    {
        $translation = $article->articleTranslations->first();
        $title = $translation?->title ?? 'Untitled';
        $summary = $translation?->summary ?? '';
        $image = $article->getFirstMediaUrl('image', 'thumb');
        
        return [
            'title' => $title,
            'summary' => $summary,
            'image' => $image,
        ];
    }
}

if (! function_exists('getSocialLinks')) {
    /**
     * 获取社交链接数据（用于 social-links 组件）
     */
    function getSocialLinks(): array
    {
        return [
            [
                'name' => 'Youtube',
                'url' => 'https://www.youtube.com/@tea-sanctuary',
                'icon' => 'youtube.svg'
            ],
            [
                'name' => 'Facebook',
                'url' => 'https://www.facebook.com/xcalderdai/',
                'icon' => 'facebook.svg'
            ],
            [
                'name' => 'Instagram',
                'url' => 'https://www.instagram.com/xcalderdai/',
                'icon' => 'instagram.svg'
            ],
            [
                'name' => 'Pinterest',
                'url' => 'https://ca.pinterest.com/calderdai/',
                'icon' => 'pinterest.svg'
            ],
            [
                'name' => 'Threads',
                'url' => 'https://www.threads.com/@xcalderdai',
                'icon' => 'threads.svg'
            ],
            [
                'name' => 'Tiktok',
                'url' => 'https://www.tiktok.com/@teanary',
                'icon' => 'tiktok.svg'
            ]
        ];
    }
}

if (! function_exists('getShareButtons')) {
    /**
     * 获取分享按钮数据（用于 share-buttons 组件）
     */
    function getShareButtons(string $url, string $title = '', string $description = '', string $image = ''): array
    {
        if (empty($title)) {
            $title = config('app.name');
        }
        
        return [
            [
                'name' => 'Facebook',
                'url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
                'icon' => 'facebook.svg'
            ],
            [
                'name' => 'Twitter',
                'url' => "https://twitter.com/intent/tweet?url=" . urlencode($url) . "&text=" . urlencode($title),
                'icon' => 'twitter.svg'
            ],
            [
                'name' => 'Instagram',
                'url' => "javascript:void(0)",
                'icon' => 'instagram.svg',
                'onclick' => "navigator.clipboard.writeText('" . $url . "'); alert('链接已复制，请在 Instagram 中分享')"
            ],
            [
                'name' => 'LinkedIn',
                'url' => "https://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($url) . "&title=" . urlencode($title),
                'icon' => 'linkedin.svg'
            ],
            [
                'name' => 'Pinterest',
                'url' => "https://pinterest.com/pin/create/button/?url=" . urlencode($url) . "&media=" . urlencode($image) . "&description=" . urlencode($description),
                'icon' => 'pinterest.svg'
            ],
            [
                'name' => 'WhatsApp',
                'url' => "https://wa.me/?text=" . urlencode($title . ' ' . $url),
                'icon' => 'whatsapp.svg'
            ],
        ];
    }
}


if (! function_exists('getPaymentStatusClasses')) {
    /**
     * 获取支付状态样式类（用于 payment/status 组件）
     */
    function getPaymentStatusClasses(string $type): array
    {
        return [
            'bgColor' => $type === 'error' ? 'bg-red-50' : 'bg-teal-50',
            'textColor' => $type === 'error' ? 'text-red-600' : 'text-teal-600',
        ];
    }
}

if (! function_exists('getTeaDecorationSizeClass')) {
    /**
     * 获取茶装饰尺寸样式类（用于 tea-decoration 组件）
     */
    function getTeaDecorationSizeClass(string $size = 'md'): string
    {
        $sizes = [
            'sm' => 'w-8 h-8',
            'md' => 'w-12 h-12',
            'lg' => 'w-16 h-16',
            'xl' => 'w-20 h-20'
        ];
        
        return $sizes[$size] ?? $sizes['md'];
    }
}

if (! function_exists('getTeaBackgroundIntensityClass')) {
    /**
     * 获取茶背景强度样式类（用于 tea-background 组件）
     */
    function getTeaBackgroundIntensityClass(string $intensity = 'light'): string
    {
        $intensities = [
            'light' => 'opacity-5',
            'medium' => 'opacity-10',
            'strong' => 'opacity-15'
        ];
        
        return $intensities[$intensity] ?? $intensities['light'];
    }
}

if (! function_exists('getSeoMetaImage')) {
    /**
     * 获取 SEO 元图片 URL（用于 layouts/seo 组件）
     */
    function getSeoMetaImage(?string $image = null): string
    {
        return $image ? asset($image) : asset('images/banner-tea.png');
    }
}

if (! function_exists('getAlertClasses')) {
    /**
     * 获取警告框样式类（用于 alert 组件）
     */
    function getAlertClasses(string $type = 'info'): array
    {
        $baseClasses = 'fixed top-20 right-6 z-50 max-w-xs w-full px-5 py-4 rounded-xl shadow-xl text-sm flex items-start space-x-3 border-l-4';
        $types = [
            'success' => 'bg-teal-50 text-teal-800 border-teal-500',
            'error' => 'bg-red-50 text-red-800 border-red-500',
            'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-500',
            'info' => 'bg-blue-50 text-blue-800 border-blue-500',
        ];
        
        return [
            'baseClasses' => $baseClasses,
            'typeClasses' => $types[$type] ?? $types['info'],
        ];
    }
}

if (! function_exists('getOrderActionButtonClass')) {
    /**
     * 获取订单操作按钮样式类（用于 order-action-buttons 组件）
     */
    function getOrderActionButtonClass(): string
    {
        return 'inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2';
    }
}

if (! function_exists('getGoogleAnalyticsId')) {
    /**
     * 获取 Google Analytics ID（用于 layouts/app 组件）
     */
    function getGoogleAnalyticsId(): string
    {
        return 'G-YQ5990WVX5';
    }
}


if (! function_exists('buildProductBreadcrumbs')) {
    /**
     * 构建产品页面面包屑（用于 product 组件）
     */
    function buildProductBreadcrumbs(?int $categoryId = null, array|\Illuminate\Support\Collection $categories = []): array
    {
        // 如果是 Collection，转换为数组
        if ($categories instanceof \Illuminate\Support\Collection) {
            $categories = $categories->toArray();
        }
        
        $breadcrumbs = [
            [
                'label' => __('app.categories'),
                'url' => locaRoute('product'),
            ],
        ];
        
        if ($categoryId && !empty($categories)) {
            $category = null;
            $parent = null;
            foreach ($categories as $cat) {
                if ($cat['id'] == $categoryId) {
                    $category = $cat;
                    break;
                }
                foreach ($cat['children'] ?? [] as $child) {
                    if ($child['id'] == $categoryId) {
                        $parent = $cat;
                        $category = $child;
                        break 2;
                    }
                }
            }
            if ($parent) {
                $breadcrumbs[] = [
                    'label' => $parent['name'],
                    'url' => locaRoute('product', ['slug' => $parent['slug']]),
                ];
            }
            if ($category) {
                $breadcrumbs[] = [
                    'label' => $category['name'],
                    'url' => '',
                ];
            }
        }
        
        return $breadcrumbs;
    }
}

if (! function_exists('buildProductDetailBreadcrumbs')) {
    /**
     * 构建产品详情页面面包屑（用于 product-detail 组件）
     */
    function buildProductDetailBreadcrumbs(string $name): array
    {
        return [
            [
                'label' => __('app.categories'),
                'url' => locaRoute('product'),
            ],
            [
                'label' => $name,
                'url' => '',
            ],
        ];
    }
}

if (! function_exists('buildSearchBreadcrumbs')) {
    /**
     * 构建搜索页面面包屑（用于 search 组件）
     */
    function buildSearchBreadcrumbs(): array
    {
        return [
            [
                'label' => __('search.title'),
                'url' => '',
            ],
        ];
    }
}


if (! function_exists('buildArticleListBreadcrumbs')) {
    /**
     * 构建文章列表页面面包屑（用于 article-list 组件）
     */
    function buildArticleListBreadcrumbs(): array
    {
        return [
            [
                'label' => __('home.article.title'),
                'url' => '',
            ],
        ];
    }
}

if (! function_exists('buildArticleDetailBreadcrumbs')) {
    /**
     * 构建文章详情页面面包屑（用于 article-detail 组件）
     */
    function buildArticleDetailBreadcrumbs($article): array
    {
        $translation = $article->articleTranslations->first();
        
        return [
            [
                'label' => __('home.article.base_name'),
                'url' => locaRoute('article.index'),
            ],
            [
                'label' => $translation?->title,
                'url' => '',
            ],
        ];
    }
}

if (! function_exists('buildUserCenterBreadcrumbs')) {
    /**
     * 构建用户中心页面面包屑
     */
    function buildUserCenterBreadcrumbs(string $page, ?string $pageLabel = null, ?string $parentLabel = null, ?string $parentUrl = null): array
    {
        $breadcrumbs = [
            [
                'label' => __('app.user_center'),
                'url' => locaRoute('auth.profile'),
            ],
        ];

        // 如果有父级页面（如订单详情页面的订单列表）
        if ($parentLabel && $parentUrl) {
            $breadcrumbs[] = [
                'label' => $parentLabel,
                'url' => $parentUrl,
            ];
        }

        // 当前页面
        if ($pageLabel) {
            $breadcrumbs[] = [
                'label' => $pageLabel,
                'url' => '',
            ];
        }

        return $breadcrumbs;
    }
}

if (! function_exists('buildManagerCenterBreadcrumbs')) {
    /**
     * 构建管理中心页面面包屑
     */
    function buildManagerCenterBreadcrumbs(string $page, ?string $pageLabel = null, ?string $parentLabel = null, ?string $parentUrl = null): array
    {
        $breadcrumbs = [
            [
                'label' => __('app.manager_center'),
                'url' => locaRoute('manager.home'),
            ],
        ];

        // 如果有父级页面（如订单详情页面的订单列表）
        if ($parentLabel && $parentUrl) {
            $breadcrumbs[] = [
                'label' => $parentLabel,
                'url' => $parentUrl,
            ];
        }

        // 当前页面
        if ($pageLabel) {
            $breadcrumbs[] = [
                'label' => $pageLabel,
                'url' => '',
            ];
        }

        return $breadcrumbs;
    }
}

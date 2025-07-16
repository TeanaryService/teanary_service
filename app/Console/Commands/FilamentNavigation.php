<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FilamentNavigation extends Command
{
    protected $signature = 'app:navigation';
    protected $description = '自动汉化、分组、换图标、设置排序（使用属性方式）';

    public function handle()
    {
        $configs = [
            'OrderResource' => [
                'label' => '订单管理',
                'group' => '商务运营',
                'icon' => 'heroicon-o-receipt-percent',
                'sort' => 100,
            ],
            'PromotionResource' => [
                'label' => '促销活动',
                'group' => '商务运营',
                'icon' => 'heroicon-o-bolt',
                'sort' => 101,
            ],
            'CartResource' => [
                'label' => '购物车',
                'group' => '商务运营',
                'icon' => 'heroicon-o-shopping-cart',
                'sort' => 102,
            ],
            'ProductResource' => [
                'label' => '商品管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-gift',
                'sort' => 200,
            ],
            'CategoryResource' => [
                'label' => '分类管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-rectangle-stack',
                'sort' => 201,
            ],
            'AttributeResource' => [
                'label' => '属性管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'sort' => 202,
            ],
            'SpecificationResource' => [
                'label' => '规格管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-puzzle-piece',
                'sort' => 203,
            ],
            'UserGroupResource' => [
                'label' => '用户分组',
                'group' => '用户管理',
                'icon' => 'heroicon-o-user-group',
                'sort' => 300,
            ],
            'UserResource' => [
                'label' => '用户管理',
                'group' => '用户管理',
                'icon' => 'heroicon-o-user',
                'sort' => 301,
            ],
            'AddressResource' => [
                'label' => '收货地址',
                'group' => '用户管理',
                'icon' => 'heroicon-o-map-pin',
                'sort' => 302,
            ],
            'LanguageResource' => [
                'label' => '语言管理',
                'group' => '系统设置',
                'icon' => 'heroicon-o-language',
                'sort' => 400,
            ],
            'CurrencyResource' => [
                'label' => '币种管理',
                'group' => '系统设置',
                'icon' => 'heroicon-o-currency-dollar',
                'sort' => 401,
            ],
            'PaymentMethodResource' => [
                'label' => '支付方式',
                'group' => '系统设置',
                'icon' => 'heroicon-o-credit-card',
                'sort' => 402,
            ],
            'ShippingMethodResource' => [
                'label' => '物流方式',
                'group' => '系统设置',
                'icon' => 'heroicon-o-truck',
                'sort' => 403,
            ],
            'CountryResource' => [
                'label' => '国家数据',
                'group' => '系统设置',
                'icon' => 'heroicon-o-globe-alt',
                'sort' => 404,
            ],
            'ZoneResource' => [
                'label' => '地区数据',
                'group' => '系统设置',
                'icon' => 'heroicon-o-globe-americas',
                'sort' => 405,
            ],
        ];

        $resourcePath = app_path('Filament/Manager/Resources');
        $files = File::files($resourcePath);

        foreach ($files as $file) {
            $fileName = $file->getFilenameWithoutExtension();

            if (!isset($configs[$fileName])) {
                $this->line("跳过未配置资源: {$fileName}");
                continue;
            }

            $content = File::get($file->getPathname());
            $config = $configs[$fileName];

            $content = $this->setStaticProperty($content, 'navigationLabel', "'{$config['label']}'");
            $content = $this->setStaticProperty($content, 'navigationGroup', "'{$config['group']}'");
            $content = $this->setStaticProperty($content, 'navigationIcon', "'{$config['icon']}'");
            $content = $this->setStaticProperty($content, 'navigationSort', $config['sort']);
            $content = $this->setStaticProperty($content, 'label', "'{$config['label']}'");
            $content = $this->setStaticProperty($content, 'pluralLabel', "'{$config['label']}'");

            File::put($file->getPathname(), $content);
            $this->info("✅ 已更新: {$fileName}");
        }

        $this->info('全部 Resource 导航属性自定义完成！');
    }

    /**
     * 替换或插入 protected static 属性
     */
    protected function setStaticProperty(string $content, string $property, $value): string
    {
        $type = $property === 'navigationSort' ? 'int' : 'string';
        $pattern = '/protected static\s+\?\s*' . $type . '\s+\$' . $property . '\s*=\s*[^;]+;/';

        if (preg_match($pattern, $content)) {
            // 替换已有属性
            return preg_replace(
                $pattern,
                "protected static ?{$type} \${$property} = {$value};",
                $content
            );
        } else {
            // 没有，插入到 class 里
            if (preg_match('/\{\s*$/m', $content)) {
                return preg_replace(
                    '/\{\s*$/m',
                    "{\n    protected static ?{$type} \${$property} = {$value};",
                    $content,
                    1
                );
            }
            return $content;
        }
    }
}

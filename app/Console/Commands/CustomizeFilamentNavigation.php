<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CustomizeFilamentNavigation extends Command
{
    protected $signature = 'app:customize-navigation';
    protected $description = '自动汉化、分组、换图标、设置排序（使用属性方式）';

    public function handle()
    {
        $configs = [
            'LanguageResource' => [
                'label' => '语言管理',
                'group' => '系统设置',
                'icon' => 'heroicon-o-language',
                'sort' => 10,
            ],
            'CurrencyResource' => [
                'label' => '币种管理',
                'group' => '系统设置',
                'icon' => 'heroicon-o-currency-dollar',
                'sort' => 20,
            ],
            'UserGroupResource' => [
                'label' => '用户分组',
                'group' => '用户管理',
                'icon' => 'heroicon-o-user-group',
                'sort' => 10,
            ],
            'UserResource' => [
                'label' => '用户管理',
                'group' => '用户管理',
                'icon' => 'heroicon-o-user',
                'sort' => 20,
            ],
            'CategoryResource' => [
                'label' => '分类管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-rectangle-stack',
                'sort' => 10,
            ],
            'AttributeResource' => [
                'label' => '属性管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'sort' => 20,
            ],
            'ProductResource' => [
                'label' => '商品管理',
                'group' => '商品管理',
                'icon' => 'heroicon-o-gift',
                'sort' => 30,
            ],
            'OrderResource' => [
                'label' => '订单管理',
                'group' => '商务运营',
                'icon' => 'heroicon-o-receipt-percent',
                'sort' => 10,
            ],
            'PromotionResource' => [
                'label' => '促销活动',
                'group' => '商务运营',
                'icon' => 'heroicon-o-bolt',
                'sort' => 20,
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

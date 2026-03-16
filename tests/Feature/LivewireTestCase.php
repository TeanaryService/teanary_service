<?php

namespace Tests\Feature;

use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Manager;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

abstract class LivewireTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * 创建并返回 Livewire 组件实例.
     */
    protected function livewire(string $component, array $params = [])
    {
        return Livewire::test($component, $params);
    }

    /**
     * 创建已认证的用户.
     */
    protected function createUser(array $attributes = [])
    {
        return User::factory()->create($attributes);
    }

    /**
     * 创建已认证的管理员.
     */
    protected function createManager(array $attributes = [])
    {
        return Manager::factory()->create($attributes);
    }

    /**
     * 创建商品
     */
    protected function createProduct(array $attributes = [])
    {
        return Product::factory()->create($attributes);
    }

    /**
     * 创建订单.
     */
    protected function createOrder(array $attributes = [])
    {
        return Order::factory()->create($attributes);
    }

    /**
     * 创建分类.
     */
    protected function createCategory(array $attributes = [])
    {
        return Category::factory()->create($attributes);
    }

    /**
     * 创建语言
     */
    protected function createLanguage(array $attributes = [])
    {
        return Language::factory()->create($attributes);
    }

    /**
     * 创建分类翻译.
     */
    protected function createCategoryTranslation(array $attributes = [])
    {
        return CategoryTranslation::factory()->create($attributes);
    }

    /**
     * 创建产品翻译.
     */
    protected function createProductTranslation(array $attributes = [])
    {
        return ProductTranslation::factory()->create($attributes);
    }

    /**
     * 创建文章翻译.
     */
    protected function createArticleTranslation(array $attributes = [])
    {
        return ArticleTranslation::factory()->create($attributes);
    }

    /**
     * 创建货币
     */
    protected function createCurrency(array $attributes = [])
    {
        return Currency::factory()->create($attributes);
    }
}

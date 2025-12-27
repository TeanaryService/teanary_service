<?php

namespace Tests\Unit;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase; // 使用 RefreshDatabase trait 确保每次测试后数据库状态干净

    /**
     * Test if a Product instance can be created using the factory.
     */
    public function test_product_can_be_created_using_factory()
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertIsString($product->slug);
        $this->assertInstanceOf(ProductStatusEnum::class, $product->status);
    }

    /**
     * Test the 'status' attribute casting.
     */
    public function test_product_status_attribute_casting()
    {
        $product = Product::factory()->create([
            'status' => ProductStatusEnum::Active,
        ]);

        $this->assertInstanceOf(ProductStatusEnum::class, $product->status);
        $this->assertEquals(ProductStatusEnum::Active, $product->status);

        $product->status = ProductStatusEnum::Inactive;
        $this->assertEquals(ProductStatusEnum::Inactive, $product->status);
    }

    /**
     * Test the 'cartItems' relationship.
     */
    public function test_cart_items_relationship()
    {
        $product = new Product; // 使用 new 避免数据库操作，仅测试关系方法
        $relation = $product->cartItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    /**
     * Test the 'productReviews' relationship.
     */
    public function test_product_reviews_relationship()
    {
        $product = new Product;
        $relation = $product->productReviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    /**
     * Test the 'orderItems' relationship.
     */
    public function test_order_items_relationship()
    {
        $product = new Product;
        $relation = $product->orderItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    /**
     * Test the 'attributeValues' relationship.
     */
    public function test_attribute_values_relationship()
    {
        $product = new Product;
        $relation = $product->attributeValues();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('product_attribute_value', $relation->getTable());
        $this->assertEquals('product_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('attribute_value_id', $relation->getRelatedPivotKeyName());
    }

    /**
     * Test the 'productCategories' relationship.
     */
    public function test_product_categories_relationship()
    {
        $product = new Product;
        $relation = $product->productCategories();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('product_category', $relation->getTable());
        $this->assertEquals('product_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('category_id', $relation->getRelatedPivotKeyName());
    }

    /**
     * Test the 'productTranslations' relationship.
     */
    public function test_product_translations_relationship()
    {
        $product = new Product;
        $relation = $product->productTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    /**
     * Test the 'productVariants' relationship.
     */
    public function test_product_variants_relationship()
    {
        $product = new Product;
        $relation = $product->productVariants();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    /**
     * Test the toSearchableArray method.
     */
    public function test_to_searchable_array()
    {
        // 创建一个产品实例
        $product = Product::factory()->create(['slug' => 'test-product']);

        // 创建并关联翻译
        $translation1 = new ProductTranslation(['name' => 'Product Name 1', 'description' => 'Description 1']);
        $translation2 = new ProductTranslation(['name' => 'Product Name 2', 'description' => 'Description 2']);

        // 模拟 productTranslations 关系
        // 使用 Collection::make() 创建一个集合
        $product->setRelation('productTranslations', Collection::make([$translation1, $translation2]));

        $searchableArray = $product->toSearchableArray();

        $this->assertIsArray($searchableArray);
        $this->assertArrayHasKey('slug', $searchableArray);
        $this->assertArrayHasKey('content', $searchableArray);
        $this->assertEquals('test-product', $searchableArray['slug']);
        $this->assertStringContainsString('Product Name 1', $searchableArray['content']);
        $this->assertStringContainsString('Description 1', $searchableArray['content']);
        $this->assertStringContainsString('Product Name 2', $searchableArray['content']);
        $this->assertStringContainsString('Description 2', $searchableArray['content']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $token;
    protected $language;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = \Illuminate\Support\Str::random(60);
        $this->manager = \App\Models\Manager::factory()->create([
            'token' => $this->token,
        ]);
        $this->language = Language::factory()->create(['code' => 'zh_CN']);
    }

    /**
     * 测试创建商品API（基础功能）.
     */
    public function test_can_create_product_with_translations()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                    'short_description' => '简短描述',
                    'description' => '详细描述',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => '商品创建成功',
        ]);

        $this->assertDatabaseHas('products', ['slug' => 'test-product']);
        $this->assertDatabaseHas('product_translations', [
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);
    }

    /**
     * 测试创建商品时自动创建分类.
     */
    public function test_can_create_product_with_new_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product-with-category',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                ],
            ],
            'categories' => [
                [
                    'slug' => 'new-category',
                    'translations' => [
                        [
                            'language_id' => $this->language->id,
                            'name' => '新分类',
                            'description' => '分类描述',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        // 验证分类已创建
        $this->assertDatabaseHas('categories', ['slug' => 'new-category']);
        $this->assertDatabaseHas('category_translations', [
            'name' => '新分类',
        ]);

        // 验证商品和分类已关联
        $product = Product::where('slug', 'test-product-with-category')->first();
        $category = Category::where('slug', 'new-category')->first();
        $this->assertTrue($product->productCategories->contains($category));
    }

    /**
     * 测试创建商品时使用已存在的分类.
     */
    public function test_can_create_product_with_existing_category()
    {
        $category = Category::factory()->create(['slug' => 'existing-category']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product-existing-category',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                ],
            ],
            'categories' => [
                [
                    'slug' => 'existing-category',
                    'translations' => [
                        [
                            'language_id' => $this->language->id,
                            'name' => '已存在分类',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        // 验证使用了已存在的分类
        $product = Product::where('slug', 'test-product-existing-category')->first();
        $this->assertTrue($product->productCategories->contains($category));
    }

    /**
     * 测试创建商品时添加分类的多语言翻译.
     */
    public function test_can_add_category_translations()
    {
        $enLanguage = Language::factory()->create(['code' => 'en']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product-multi-lang-category',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                ],
            ],
            'categories' => [
                [
                    'slug' => 'multi-lang-category',
                    'translations' => [
                        [
                            'language_id' => $this->language->id,
                            'name' => '中文分类',
                        ],
                        [
                            'language_id' => $enLanguage->id,
                            'name' => 'English Category',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $category = Category::where('slug', 'multi-lang-category')->first();
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'language_id' => $this->language->id,
            'name' => '中文分类',
        ]);
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'language_id' => $enLanguage->id,
            'name' => 'English Category',
        ]);
    }

    /**
     * 测试创建商品时处理主图和内容图片.
     */
    public function test_can_create_product_with_images()
    {
        Storage::fake('public');

        // 最小的有效 1x1 透明 PNG base64
        // 这是一个有效的 PNG 图片数据
        $placeholder = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product-with-images',
            'main_image' => [
                'image_id' => 'main-img-1',
                'contents' => $placeholder,
            ],
            'content_images' => [
                [
                    'image_id' => 'content-img-1',
                    'original_url' => 'http://example.com/img1.jpg',
                    'contents' => $placeholder,
                ],
            ],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                    'description' => '描述包含图片：{{image:content-img-1}}',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $product = Product::where('slug', 'test-product-with-images')->first();
        $this->assertNotNull($product->getFirstMedia('images'));
        $this->assertCount(1, $product->getMedia('content-images'));

        // 验证图片占位符已被替换
        $translation = $product->productTranslations()->first();
        $this->assertStringNotContainsString('{{image:content-img-1}}', $translation->description);
        $this->assertStringContainsString('/storage', $translation->description);
    }

    /**
     * 测试验证错误 - slug 重复.
     */
    public function test_validation_error_duplicate_slug()
    {
        Product::factory()->create(['slug' => 'existing-slug']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'existing-slug',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    }

    /**
     * 测试验证错误 - 缺少翻译.
     */
    public function test_validation_error_missing_translations()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['translations']);
    }

    /**
     * 测试验证错误 - SKU 重复.
     */
    public function test_validation_error_duplicate_sku()
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'EXISTING-SKU']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/products/add', [
            'slug' => 'test-product',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $this->language->id,
                    'name' => '测试商品',
                ],
            ],
            'variants' => [
                [
                    'sku' => 'EXISTING-SKU',
                    'price' => 99.99,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['variants.0.sku']);
    }
}

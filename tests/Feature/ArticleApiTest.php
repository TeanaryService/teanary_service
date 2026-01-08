<?php

namespace Tests\Feature;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature测试示例.
 *
 * Feature测试用于测试完整的功能流程，包括：
 * - HTTP请求和响应
 * - 路由和控制器
 * - 中间件
 * - 数据库交互
 * - 认证和授权
 *
 * 与Unit测试的区别：
 * - Unit测试：测试单个类或方法，通常使用Mock
 * - Feature测试：测试完整功能，包括HTTP层、数据库等
 */
class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试创建文章API
     * 注意：根据routes/api.php，只有POST /api/articles/add路由.
     */
    public function test_can_create_article()
    {
        $token = \Illuminate\Support\Str::random(60);
        $user = \App\Models\Manager::factory()->create([
            'token' => $token,
        ]);
        $language = Language::factory()->create(['code' => 'en']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/articles/add', [
            'slug' => 'test-article',
            'main_image' => ['image_id' => 'test-main-image', 'contents' => 'R0lGODlhAQABAIAAAO/v7wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='],
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'summary' => 'Test Summary',
                    'content' => 'Test Content',
                ],
            ],
        ]);

        // 根据实际控制器返回的响应调整断言
        $response->assertStatus(201);
        $this->assertDatabaseHas('articles', ['slug' => 'test-article']);
    }
}

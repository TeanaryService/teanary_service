<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('error')->byDefault();
    }

    public function test_store_creates_article_successfully(): void
    {
        $language = Language::factory()->create();

        $response = $this->postJson('/api/articles/add', [
            'slug' => 'test-article',
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'content' => 'Test content',
                    'summary' => 'Test summary',
                ]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'slug',
                'article_translations',
            ]
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/articles/add', []);

        $response->assertStatus(422);
    }

    public function test_store_handles_main_image(): void
    {
        $language = Language::factory()->create();
        $imageContent = base64_encode('fake image content');

        $response = $this->postJson('/api/articles/add', [
            'slug' => 'test-article',
            'main_image' => [
                'contents' => $imageContent,
                'image_id' => 'test-image-123',
            ],
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'content' => 'Test content',
                ]
            ]
        ]);

        $response->assertStatus(201);
    }

    public function test_store_handles_content_images(): void
    {
        $language = Language::factory()->create();
        $imageContent = base64_encode('fake image content');

        $response = $this->postJson('/api/articles/add', [
            'slug' => 'test-article',
            'content_images' => [
                [
                    'contents' => $imageContent,
                    'image_id' => 'content-image-123',
                    'original_url' => 'https://example.com/image.jpg',
                ]
            ],
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'content' => 'Test content with {{image:content-image-123}}',
                ]
            ]
        ]);

        $response->assertStatus(201);
    }

    public function test_store_handles_exception_gracefully(): void
    {
        // 测试验证失败的情况（缺少必要字段）
        $response = $this->postJson('/api/articles/add', [
            'slug' => 'test-article',
            'translations' => [] // 空数组会导致验证失败
        ]);

        $response->assertStatus(422); // 验证错误
    }
}


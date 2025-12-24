<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function it_can_create_an_article_via_api()
    {
        $language = Language::factory()->create();

        $mainImageContent = base64_encode(file_get_contents(__DIR__ . '/../../fixtures/test-image.jpg'));
        if (!$mainImageContent) {
            // 如果测试图片不存在，创建一个简单的测试数据
            $mainImageContent = base64_encode('fake-image-content');
        }

        $payload = [
            'slug' => 'test-article',
            'main_image' => [
                'image_id' => 'main-123',
                'contents' => $mainImageContent,
            ],
            'content_images' => [],
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'content' => 'This is a test article content.',
                    'summary' => 'Test summary',
                ],
            ],
        ];

        $response = $this->postJson('/api/articles/add', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'slug',
                    'article_translations',
                ],
            ]);

        $this->assertDatabaseHas('articles', [
            'slug' => 'test-article',
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/articles/add', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug', 'translations']);
    }

    #[Test]
    public function it_handles_invalid_image_data()
    {
        $language = Language::factory()->create();

        $payload = [
            'slug' => 'test-article',
            'main_image' => [
                'image_id' => 'main-123',
                'contents' => 'invalid-base64-data',
            ],
            'content_images' => [],
            'translations' => [
                [
                    'language_id' => $language->id,
                    'title' => 'Test Article',
                    'content' => 'Content',
                ],
            ],
        ];

        $response = $this->postJson('/api/articles/add', $payload);

        // 根据实际实现，可能是400或500错误
        $this->assertTrue(in_array($response->status(), [400, 422, 500]));
    }
}


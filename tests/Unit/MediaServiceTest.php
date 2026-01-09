<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MediaService();
        Storage::fake('public');
    }

    /**
     * 测试：解码有效的 base64 图片
     */
    public function test_decode_base64_image_with_valid_data()
    {
        $base64Data = base64_encode('fake-image-content');
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('decodeBase64Image');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $base64Data);

        $this->assertEquals('fake-image-content', $result);
    }

    /**
     * 测试：解码无效的 base64 数据抛出异常
     */
    public function test_decode_base64_image_with_invalid_data_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无效的 base64 图片数据');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('decodeBase64Image');
        $method->setAccessible(true);

        $method->invoke($this->service, 'invalid-base64-data!!!');
    }

    /**
     * 测试：从URL下载图片
     */
    public function test_download_image_from_url()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'example.com/image.jpg' => Http::response($pngData, 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('downloadImageFromUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'http://example.com/image.jpg');

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    /**
     * 测试：下载图片失败抛出异常
     */
    public function test_download_image_from_url_handles_failure()
    {
        Http::fake([
            'example.com/image.jpg' => Http::response('', 404),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('无法下载图片');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('downloadImageFromUrl');
        $method->setAccessible(true);

        $method->invoke($this->service, 'http://example.com/image.jpg');
    }

    /**
     * 测试：下载空内容抛出异常
     */
    public function test_download_image_from_url_handles_empty_content()
    {
        Http::fake([
            'example.com/image.jpg' => Http::response('', 200),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('下载的图片内容为空');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('downloadImageFromUrl');
        $method->setAccessible(true);

        $method->invoke($this->service, 'http://example.com/image.jpg');
    }

    /**
     * 测试：从URL获取图片扩展名
     */
    public function test_get_image_extension_from_url()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getImageExtensionFromUrl');
        $method->setAccessible(true);

        $this->assertEquals('jpg', $method->invoke($this->service, 'http://example.com/image.jpg'));
        $this->assertEquals('png', $method->invoke($this->service, 'http://example.com/image.png'));
        $this->assertEquals('gif', $method->invoke($this->service, 'http://example.com/image.gif'));
        $this->assertEquals('webp', $method->invoke($this->service, 'http://example.com/image.webp'));
    }

    /**
     * 测试：从URL获取无效扩展名返回默认值
     */
    public function test_get_image_extension_from_url_returns_default_for_invalid()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getImageExtensionFromUrl');
        $method->setAccessible(true);

        $this->assertEquals('jpg', $method->invoke($this->service, 'http://example.com/image.unknown'));
        $this->assertEquals('jpg', $method->invoke($this->service, 'http://example.com/image'));
    }

    /**
     * 测试：处理单个主图（base64）
     */
    public function test_handle_main_image_with_base64()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $product = Product::factory()->create();
        $base64Data = base64_encode($pngData);

        $this->service->handleMainImage($product, [
            'image_id' => 'test-image-1',
            'contents' => $base64Data,
        ]);

        $this->assertCount(1, $product->getMedia('images'));
    }

    /**
     * 测试：处理单个主图（URL）
     */
    public function test_handle_main_image_with_url()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'example.com/image.jpg' => Http::response($pngData, 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $product = Product::factory()->create();

        $this->service->handleMainImage($product, [
            'image_id' => 'test-image-1',
            'image_url' => 'http://example.com/image.jpg',
        ]);

        $this->assertCount(1, $product->getMedia('images'));
    }

    /**
     * 测试：处理多个主图
     */
    public function test_handle_main_image_with_multiple_images()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $product = Product::factory()->create();
        $base64Data = base64_encode($pngData);

        $this->service->handleMainImage($product, [
            [
                'image_id' => 'test-image-1',
                'contents' => $base64Data,
            ],
            [
                'image_id' => 'test-image-2',
                'contents' => $base64Data,
            ],
        ]);

        $this->assertCount(2, $product->getMedia('images'));
    }

    /**
     * 测试：处理空主图
     */
    public function test_handle_main_image_with_null()
    {
        $product = Product::factory()->create();

        $this->service->handleMainImage($product, null);

        $this->assertCount(0, $product->getMedia('images'));
    }

    /**
     * 测试：处理内容图片
     */
    public function test_handle_content_images()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $product = Product::factory()->create();
        $base64Data = base64_encode($pngData);

        $imageMap = $this->service->handleContentImages($product, [
            [
                'image_id' => 'content-1',
                'contents' => $base64Data,
            ],
            [
                'image_id' => 'content-2',
                'contents' => $base64Data,
            ],
        ]);

        $this->assertCount(2, $imageMap);
        $this->assertArrayHasKey('content-1', $imageMap);
        $this->assertArrayHasKey('content-2', $imageMap);
        $this->assertCount(2, $product->getMedia('content-images'));
    }

    /**
     * 测试：处理空内容图片数组
     */
    public function test_handle_content_images_with_empty_array()
    {
        $product = Product::factory()->create();

        $imageMap = $this->service->handleContentImages($product, []);

        $this->assertEmpty($imageMap);
        $this->assertCount(0, $product->getMedia('content-images'));
    }

    /**
     * 测试：处理内容图片跳过缺少 image_id 的项
     */
    public function test_handle_content_images_skips_missing_image_id()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $product = Product::factory()->create();
        $base64Data = base64_encode($pngData);

        $imageMap = $this->service->handleContentImages($product, [
            [
                'image_id' => 'content-1',
                'contents' => $base64Data,
            ],
            [
                // 缺少 image_id
                'contents' => $base64Data,
            ],
        ]);

        $this->assertCount(1, $imageMap);
        $this->assertArrayHasKey('content-1', $imageMap);
    }

    /**
     * 测试：替换图片占位符
     */
    public function test_replace_image_placeholders()
    {
        $content = 'This is an image {{image:test-1}} and another {{image:test-2}}';
        $imageMap = [
            'test-1' => '/storage/media/test-1.jpg',
            'test-2' => '/storage/media/test-2.jpg',
        ];

        $result = $this->service->replaceImagePlaceholders($content, $imageMap);

        $this->assertStringContainsString('<img src="/storage/media/test-1.jpg"', $result);
        $this->assertStringContainsString('<img src="/storage/media/test-2.jpg"', $result);
    }

    /**
     * 测试：替换图片占位符处理各种格式
     */
    public function test_replace_image_placeholders_handles_various_formats()
    {
        $content = '{{image:test-1}} {{ image:test-1 }} {{image: test-1}} {{ image: test-1 }}';
        $imageMap = [
            'test-1' => '/storage/media/test-1.jpg',
        ];

        $result = $this->service->replaceImagePlaceholders($content, $imageMap);

        // 所有格式都应该被替换
        $this->assertStringNotContainsString('{{image:test-1}}', $result);
        $this->assertStringNotContainsString('{{ image:test-1 }}', $result);
        $this->assertStringContainsString('<img', $result);
    }

    /**
     * 测试：替换图片占位符处理空内容
     */
    public function test_replace_image_placeholders_with_null_content()
    {
        $result = $this->service->replaceImagePlaceholders(null, ['test-1' => '/storage/media/test-1.jpg']);

        $this->assertEquals('', $result);
    }

    /**
     * 测试：替换图片占位符处理空映射
     */
    public function test_replace_image_placeholders_with_empty_map()
    {
        $content = 'This is an image {{image:test-1}}';
        $result = $this->service->replaceImagePlaceholders($content, []);

        $this->assertEquals($content, $result);
    }

    /**
     * 测试：处理内容图片返回URL映射
     */
    public function test_handle_content_images_returns_url_map()
    {
        // 创建一个简单的有效图片数据（1x1 PNG）
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        $product = Product::factory()->create();
        $base64Data = base64_encode($pngData);

        $imageMap = $this->service->handleContentImages($product, [
            [
                'image_id' => 'content-1',
                'contents' => $base64Data,
            ],
        ]);

        $this->assertIsArray($imageMap);
        $this->assertArrayHasKey('content-1', $imageMap);
        $this->assertIsString($imageMap['content-1']);
        $this->assertStringContainsString('/storage', $imageMap['content-1']);
    }
}

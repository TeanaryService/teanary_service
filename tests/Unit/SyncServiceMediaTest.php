<?php

namespace Tests\Unit;

use App\Jobs\ResizeUploadedImage;
use App\Models\Media;
use App\Models\Product;
use App\Services\SnowflakeService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncServiceMediaTest extends TestCase
{
    use RefreshDatabase;

    protected SyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 使用 fake storage
        Storage::fake('public');
        
        $this->service = new SyncService;

        // 配置同步服务
        Config::set('sync.enabled', true);
        Config::set('sync.node', 'node1');
        Config::set('sync.remote_nodes', [
            'node2' => [
                'url' => 'https://node2.example.com',
                'api_key' => 'test-api-key',
                'timeout' => 600,
            ],
        ]);
        Config::set('sync.sync_models', [
            Media::class,
            Product::class,
        ]);
    }

    /**
     * 测试：同步 Media 模型时下载图片文件
     */
    public function test_sync_media_downloads_image_file()
    {
        // 创建一个产品作为 Media 的关联模型
        $product = Product::factory()->create();
        
        // 创建一个最小的有效 PNG 图片（1x1 透明像素）
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        
        // 模拟 HTTP 响应，返回图片内容
        Http::fake([
            'node2.example.com/storage/*' => Http::response($imageContent, 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $mediaId = app(SnowflakeService::class)->nextId();
        
        // 准备同步数据，包含 original_url（不预先创建 Media，让同步来创建）
        $mediaData = [
            'id' => $mediaId,
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection_name' => 'images',
            'name' => 'test-image',
            'file_name' => 'test-image.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'size' => strlen($imageContent),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        $mediaData['original_url'] = 'https://node2.example.com/storage/test-image.png';

        $batchData = [
            [
                'model_type' => Media::class,
                'model_id' => $mediaId,
                'action' => 'created',
                'payload' => $mediaData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        // 执行同步
        $result = $this->service->receiveBatchSync($batchData);

        // 验证同步成功
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);

        // 验证文件已下载并保存
        $media = Media::find($mediaId);
        $this->assertNotNull($media);
        // 使用SyncService的getMediaFilePath方法获取正确的路径
        $reflection = new \ReflectionClass($this->service);
        $getMediaFilePathMethod = $reflection->getMethod('getMediaFilePath');
        $getMediaFilePathMethod->setAccessible(true);
        $filePath = $getMediaFilePathMethod->invoke($this->service, $media);
        $this->assertTrue(Storage::disk('public')->exists($filePath));
        
        // 验证文件内容正确
        $savedContent = Storage::disk('public')->get($filePath);
        $this->assertEquals($imageContent, $savedContent);
    }

    /**
     * 测试：缺少 original_url 时跳过下载
     */
    public function test_sync_media_skips_download_when_missing_original_url()
    {
        // 创建一个产品作为 Media 的关联模型
        $product = Product::factory()->create();

        $mediaId = app(SnowflakeService::class)->nextId();
        
        // 准备同步数据，不包含 original_url
        // 注意：不先创建 Media，让同步来创建
        $mediaData = [
            'id' => $mediaId,
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection_name' => 'images',
            'name' => 'test-image',
            'file_name' => 'test-image.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'size' => 100,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        // 故意不设置 original_url

        $batchData = [
            [
                'model_type' => Media::class,
                'model_id' => $mediaId,
                'action' => 'created',
                'payload' => $mediaData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        // 执行同步
        $result = $this->service->receiveBatchSync($batchData);

        // 验证同步成功（但文件未下载）
        $this->assertEquals(1, $result['success']);
        
        // 验证 Media 已创建
        $media = Media::find($mediaId);
        $this->assertNotNull($media);
        
        // 验证文件不存在（因为没有 original_url，不会下载）
        $reflection = new \ReflectionClass($this->service);
        $getMediaFilePathMethod = $reflection->getMethod('getMediaFilePath');
        $getMediaFilePathMethod->setAccessible(true);
        $filePath = $getMediaFilePathMethod->invoke($this->service, $media);
        $this->assertFalse(Storage::disk('public')->exists($filePath));
    }

    /**
     * 测试：ResizeUploadedImage Job 处理文件不存在的情况
     */
    public function test_resize_job_handles_missing_file_gracefully()
    {
        // 创建一个产品作为 Media 的关联模型
        $product = Product::factory()->create();

        $mediaId = app(SnowflakeService::class)->nextId();
        
        // 创建 Media 记录（但不保存文件）
        $media = new Media([
            'id' => $mediaId,
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection_name' => 'images',
            'name' => 'test-image',
            'file_name' => 'test-image.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'size' => 100,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);
        $media->save();

        // 验证文件不存在
        $reflection = new \ReflectionClass($this->service);
        $getMediaFilePathMethod = $reflection->getMethod('getMediaFilePath');
        $getMediaFilePathMethod->setAccessible(true);
        $filePath = $getMediaFilePathMethod->invoke($this->service, $media);
        $this->assertFalse(Storage::disk('public')->exists($filePath));

        // 执行 ResizeUploadedImage Job，应该优雅地跳过
        $job = new ResizeUploadedImage($media);
        $job->handle();

        // 验证没有抛出异常，文件仍然不存在
        $this->assertFalse(Storage::disk('public')->exists($filePath));
    }
}

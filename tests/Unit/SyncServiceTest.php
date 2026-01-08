<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SyncLog;
use App\Models\SyncStatus;
use App\Services\SyncService;
use App\Services\SnowflakeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SyncService();
        
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
            Product::class,
        ]);
    }

    public function test_record_sync_creates_sync_log_for_enabled_model()
    {
        $product = Product::factory()->create();
        
        $this->service->recordSync($product, 'created', 'node1');
        
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
        ]);
    }

    public function test_record_sync_skips_when_sync_disabled()
    {
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        
        $this->service->recordSync($product, 'created', 'node1');
        
        $this->assertDatabaseMissing('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);
    }

    public function test_record_sync_skips_when_model_not_in_sync_list()
    {
        Config::set('sync.sync_models', []);
        $product = Product::factory()->create();
        
        $this->service->recordSync($product, 'created', 'node1');
        
        $this->assertDatabaseMissing('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);
    }

    public function test_record_sync_skips_when_sync_status_up_to_date()
    {
        $product = Product::factory()->create();
        $syncHash = md5(json_encode($product->toArray()));
        
        SyncStatus::create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'node' => 'node2',
            'sync_hash' => $syncHash,
        ]);
        
        $this->service->recordSync($product, 'updated', 'node1');
        
        // 应该不会创建新的同步日志（因为哈希值相同）
        $this->assertDatabaseCount('sync_logs', 0);
    }

    public function test_record_batch_sync_creates_multiple_sync_logs()
    {
        $products = Product::factory()->count(3)->create();
        
        $models = $products->map(function ($product) {
            return ['model' => $product, 'action' => 'created'];
        })->toArray();
        
        $this->service->recordBatchSync($models, 'node1');
        
        $this->assertDatabaseCount('sync_logs', 3);
    }

    public function test_record_batch_sync_skips_empty_array()
    {
        $this->service->recordBatchSync([], 'node1');
        
        $this->assertDatabaseCount('sync_logs', 0);
    }

    public function test_sync_batch_to_remote_sends_http_request()
    {
        Http::fake([
            'node2.example.com/api/sync/batch' => Http::response([
                'success' => 1,
                'failed' => 0,
                'results' => [
                    ['index' => 0, 'sync_log_id' => 1, 'success' => true],
                ],
            ], 200),
        ]);
        
        $product = Product::factory()->create();
        $syncLog = SyncLog::create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
            'payload' => $product->toArray(),
        ]);
        
        $result = $this->service->syncBatchToRemote(collect([$syncLog]), 'node2');
        
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        
        Http::assertSent(function ($request) {
            return $request->url() === 'https://node2.example.com/api/sync/batch' &&
                   $request->hasHeader('X-API-Key', 'test-api-key');
        });
    }

    public function test_sync_batch_to_remote_handles_failure()
    {
        Http::fake([
            'node2.example.com/api/sync/batch' => Http::response([], 500),
        ]);
        
        $product = Product::factory()->create();
        $syncLog = new SyncLog([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
            'payload' => $product->toArray(),
        ]);
        $syncLog->save();
        
        $result = $this->service->syncBatchToRemote(collect([$syncLog]), 'node2');
        
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertNotEmpty($result['errors']);
        
        $syncLog->refresh();
        $this->assertEquals('failed', $syncLog->status);
    }

    public function test_receive_batch_sync_creates_models()
    {
        $product = Product::factory()->make();
        $productId = app(SnowflakeService::class)->nextId();
        
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => array_merge($product->toArray(), ['id' => $productId]),
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        $this->assertDatabaseHas('products', ['id' => $productId]);
    }

    public function test_receive_batch_sync_updates_existing_models()
    {
        $product = Product::factory()->create();
        $updatedData = $product->toArray();
        $updatedData['status'] = 'inactive';
        
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $updatedData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        
        $product->refresh();
        $this->assertEquals('inactive', $product->status);
    }

    public function test_receive_batch_sync_deletes_models()
    {
        $product = Product::factory()->create();
        
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'deleted',
                'payload' => ['id' => $product->id],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_receive_batch_sync_skips_when_local_data_newer()
    {
        $product = Product::factory()->create();
        $product->updated_at = now();
        $product->save();
        
        $oldTimestamp = now()->subHour()->toIso8601String();
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $product->toArray(),
                'source_node' => 'node2',
                'timestamp' => $oldTimestamp,
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(1, $result['success']);
        $this->assertTrue($result['results'][0]['skipped'] ?? false);
    }

    public function test_receive_batch_sync_handles_validation_errors()
    {
        $batchData = [
            [
                'model_type' => 'InvalidModel',
                'model_id' => 123,
                'action' => 'created',
                'payload' => [],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertStringContainsString('模型不在同步列表中', $result['results'][0]['error'] ?? '');
    }

    public function test_receive_batch_sync_handles_missing_model_type()
    {
        $batchData = [
            [
                'model_id' => 123,
                'action' => 'created',
                'payload' => [],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertEquals('缺少 model_type', $result['results'][0]['error'] ?? '');
    }

    public function test_receive_batch_sync_clears_cache()
    {
        Cache::put('test_key', 'test_value', 60);
        
        $product = Product::factory()->make();
        $productId = app(SnowflakeService::class)->nextId();
        
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => array_merge($product->toArray(), ['id' => $productId]),
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $this->service->receiveBatchSync($batchData);
        
        $this->assertNull(Cache::get('test_key'));
    }

    public function test_receive_batch_sync_groups_by_model_type()
    {
        $products = Product::factory()->count(2)->create();
        $productIds = $products->pluck('id');
        
        $batchData = $products->map(function ($product, $index) {
            return [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $product->toArray(),
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ];
        })->toArray();
        
        $result = $this->service->receiveBatchSync($batchData);
        
        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);
    }

    public function test_receive_batch_sync_handles_unique_field_conflict()
    {
        // 创建一个已有slug的产品
        $existingProduct = Product::factory()->create(['slug' => 'test-product']);
        
        // 尝试同步一个相同slug但不同ID的产品
        $newProductId = app(SnowflakeService::class)->nextId();
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $newProductId,
                'action' => 'created',
                'payload' => [
                    'id' => $newProductId,
                    'slug' => 'test-product', // 相同的slug
                    'status' => 'active',
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
        
        $result = $this->service->receiveBatchSync($batchData);
        
        // 应该成功处理（通过唯一字段找到现有记录并更新）
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        
        // 现有产品应该被更新，而不是创建新记录
        $this->assertDatabaseCount('products', 1);
    }
}

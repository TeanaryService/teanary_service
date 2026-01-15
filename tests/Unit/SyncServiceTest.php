<?php

namespace Tests\Unit;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use App\Models\SyncLog;
use App\Models\SyncStatus;
use App\Services\SnowflakeService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
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
        // 清理之前的测试数据
        SyncLog::truncate();
        SyncStatus::truncate();

        $product = Product::factory()->create();

        // 先记录一次同步，获取正确的哈希值
        $this->service->recordSync($product, 'updated', 'node1');

        // 获取刚创建的同步日志的哈希值
        $syncLog = SyncLog::first();
        $this->assertNotNull($syncLog);

        // 使用反射调用 protected 方法生成哈希
        $reflection = new \ReflectionClass($this->service);
        $generateHashMethod = $reflection->getMethod('generateSyncHash');
        $generateHashMethod->setAccessible(true);

        $syncHash = $generateHashMethod->invoke($this->service, $product, 'updated');

        // 创建同步状态
        SyncStatus::create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'node' => 'node2',
            'sync_hash' => $syncHash,
        ]);

        // 清理之前的日志
        SyncLog::truncate();

        // 再次记录同步，应该跳过（因为哈希值相同）
        $this->service->recordSync($product, 'updated', 'node1');

        // 应该不会创建新的同步日志（因为哈希值相同）
        $this->assertDatabaseCount('sync_logs', 0);
    }

    public function test_record_batch_sync_skips_empty_array()
    {
        $this->service->recordBatchSync([], 'node1');

        $this->assertDatabaseCount('sync_logs', 0);
    }

    public function test_sync_batch_to_remote_sends_http_request()
    {
        Http::fake([
            'node2.example.com/api/sync/receive-batch' => Http::response([
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
            return str_contains($request->url(), '/api/sync/receive-batch') &&
                   $request->hasHeader('Authorization');
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
        $productId = app(SnowflakeService::class)->nextId();
        $product = Product::factory()->make();
        $productData = $product->toArray();
        $productData['id'] = $productId;
        // 确保包含所有必需的字段
        if (! isset($productData['status'])) {
            $productData['status'] = 'active';
        }
        // 移除可能干扰的字段
        unset($productData['product_variants'], $productData['product_translations']);

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => $productData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
        // 验证产品已创建（ID可能因为HasSnowflakeId trait而不同，但应该有一条记录）
        $this->assertDatabaseCount('products', 1);
        $createdProduct = Product::first();
        $this->assertNotNull($createdProduct);
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
        // status 是枚举类型，需要比较枚举值
        $this->assertEquals(ProductStatusEnum::Inactive, $product->status);
    }

    public function test_receive_batch_sync_deletes_models()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

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
        // Product 没有软删除，使用硬删除
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_receive_batch_sync_skips_when_local_data_newer()
    {
        $product = Product::factory()->create();
        $product->updated_at = now();
        $product->save();

        $oldTimestamp = now()->subHour()->toIso8601String();
        $updatedData = $product->toArray();
        $updatedData['status'] = 'inactive';
        
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $updatedData,
                'source_node' => 'node2',
                'timestamp' => $oldTimestamp,
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        // 更新操作不应该跳过，即使本地数据更新，也要执行同步
        // 原因：如果源数据有多个更新，同步到远程时，如果第一个更新后本地数据的时间戳更新了，
        // 后续的更新就会被错误地跳过，导致数据不一致
        $this->assertEquals(1, $result['success']);
        // 验证没有 skipped 字段（或者 skipped 为 false）
        $this->assertFalse(isset($result['results'][0]['skipped']) ? $result['results'][0]['skipped'] : false);
        
        // 验证数据已更新
        $product->refresh();
        $this->assertEquals(ProductStatusEnum::Inactive, $product->status);
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

    /**
     * 测试：recordSync 处理 deleted action
     */
    public function test_record_sync_handles_deleted_action()
    {
        $product = Product::factory()->create();
        $productId = $product->id;
        
        // 删除产品
        $product->delete();

        $this->service->recordSync($product, 'deleted', 'node1');

        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $productId,
            'action' => 'deleted',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
        ]);
    }

    /**
     * 测试：recordSync 处理多个目标节点
     */
    public function test_record_sync_creates_logs_for_multiple_target_nodes()
    {
        SyncLog::truncate();
        SyncStatus::truncate();

        Config::set('sync.remote_nodes', [
            'node2' => [
                'url' => 'https://node2.example.com',
                'api_key' => 'test-api-key-2',
                'timeout' => 600,
            ],
            'node3' => [
                'url' => 'https://node3.example.com',
                'api_key' => 'test-api-key-3',
                'timeout' => 600,
            ],
        ]);

        // 临时禁用同步，避免创建产品时自动触发同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        $this->service->recordSync($product, 'created', 'node1');

        // 应该为每个目标节点创建一条日志
        $this->assertDatabaseCount('sync_logs', 2);
        $this->assertDatabaseHas('sync_logs', [
            'target_node' => 'node2',
        ]);
        $this->assertDatabaseHas('sync_logs', [
            'target_node' => 'node3',
        ]);
    }

    /**
     * 测试：recordSync 处理空目标节点列表
     */
    public function test_record_sync_handles_empty_target_nodes()
    {
        Config::set('sync.remote_nodes', []);
        Config::set('sync.node', 'node1');

        $product = Product::factory()->create();
        $this->service->recordSync($product, 'created', 'node1');

        // 应该不创建任何日志（因为没有目标节点）
        $this->assertDatabaseCount('sync_logs', 0);
    }

    /**
     * 测试：recordBatchSync 跳过不在同步列表的模型
     */
    public function test_record_batch_sync_skips_models_not_in_sync_list()
    {
        SyncLog::truncate();
        SyncStatus::truncate();

        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $category = \App\Models\Category::factory()->create();
        Config::set('sync.enabled', true);

        // 只同步 Product，不同步 Category
        Config::set('sync.sync_models', [Product::class]);

        $models = [
            ['model' => $product, 'action' => 'created'],
            ['model' => $category, 'action' => 'created'],
        ];

        $this->service->recordBatchSync($models, 'node1');

        // 应该只创建1条日志（Category不在同步列表中）
        $this->assertDatabaseCount('sync_logs', 1);
        $this->assertDatabaseHas('sync_logs', [
            'model_id' => $product->id,
            'model_type' => Product::class,
        ]);
    }

    /**
     * 测试：syncBatchToRemote 处理空集合
     */
    public function test_sync_batch_to_remote_handles_empty_collection()
    {
        $result = $this->service->syncBatchToRemote(collect([]), 'node2');

        $this->assertEquals(0, $result['success']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * 测试：syncBatchToRemote 处理部分成功部分失败
     */
    public function test_sync_batch_to_remote_handles_partial_success()
    {
        $products = Product::factory()->count(2)->create();
        $syncLogs = $products->map(function ($product) {
            return SyncLog::create([
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'created',
                'source_node' => 'node1',
                'target_node' => 'node2',
                'status' => 'pending',
                'payload' => $product->toArray(),
            ]);
        });

        // 使用实际的 syncLog ID
        Http::fake([
            'node2.example.com/api/sync/receive-batch' => Http::response([
                'success' => 1,
                'failed' => 1,
                'results' => [
                    ['index' => 0, 'sync_log_id' => $syncLogs[0]->id, 'success' => true],
                    ['index' => 1, 'sync_log_id' => $syncLogs[1]->id, 'success' => false, 'error' => '处理失败'],
                ],
            ], 200),
        ]);

        $result = $this->service->syncBatchToRemote($syncLogs, 'node2');

        $this->assertEquals(1, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertNotEmpty($result['errors']);

        // 验证第一个日志成功
        $syncLogs[0]->refresh();
        $this->assertEquals('completed', $syncLogs[0]->status);

        // 验证第二个日志失败
        $syncLogs[1]->refresh();
        $this->assertEquals('failed', $syncLogs[1]->status);
    }

    /**
     * 测试：syncBatchToRemote 处理无效的远程节点配置
     */
    public function test_sync_batch_to_remote_handles_invalid_remote_config()
    {
        $product = Product::factory()->create();
        $syncLog = SyncLog::create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'invalid-node',
            'status' => 'pending',
            'payload' => $product->toArray(),
        ]);

        $result = $this->service->syncBatchToRemote(collect([$syncLog]), 'invalid-node');

        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertStringContainsString('远程节点配置不存在', $result['errors'][0] ?? '');
    }

    /**
     * 测试：syncBatchToRemote 处理响应格式错误（没有 results）
     */
    public function test_sync_batch_to_remote_handles_missing_results_in_response()
    {
        Http::fake([
            'node2.example.com/api/sync/receive-batch' => Http::response([
                'success' => true,
                // 没有 results 字段
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

        // 如果没有详细结果，假设全部成功
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);

        $syncLog->refresh();
        $this->assertEquals('completed', $syncLog->status);
    }

    /**
     * 测试：receiveBatchSync 处理空数组输入
     */
    public function test_receive_batch_sync_handles_empty_array_input()
    {
        $result = $this->service->receiveBatchSync([]);
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(0, $result['failed']);
    }

    /**
     * 测试：receiveBatchSync 处理混合模型类型
     */
    public function test_receive_batch_sync_handles_mixed_model_types()
    {
        Config::set('sync.sync_models', [
            Product::class,
            \App\Models\Category::class,
        ]);

        $productId = app(SnowflakeService::class)->nextId();
        $product = Product::factory()->make();
        $productData = $product->toArray();
        $productData['id'] = $productId;
        if (! isset($productData['status'])) {
            $productData['status'] = 'active';
        }
        unset($productData['product_variants'], $productData['product_translations']);

        $categoryId = app(SnowflakeService::class)->nextId();
        $category = \App\Models\Category::factory()->make();
        $categoryData = $category->toArray();
        $categoryData['id'] = $categoryId;

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => $productData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'model_type' => \App\Models\Category::class,
                'model_id' => $categoryId,
                'action' => 'created',
                'payload' => $categoryData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('categories', 1);
    }

    /**
     * 测试：receiveBatchSync 处理时间戳边界情况（相同时间）
     */
    public function test_receive_batch_sync_handles_same_timestamp()
    {
        $product = Product::factory()->create();
        $product->updated_at = now();
        $product->save();

        $sameTimestamp = $product->updated_at->toIso8601String();
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $product->toArray(),
                'source_node' => 'node2',
                'timestamp' => $sameTimestamp,
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        // 相同时间戳应该同步（本地时间不大于远程时间）
        $this->assertEquals(1, $result['success']);
        $this->assertFalse($result['results'][0]['skipped'] ?? false);
    }

    /**
     * 测试：receiveBatchSync 处理时间戳为 null 的情况
     */
    public function test_receive_batch_sync_handles_null_timestamp()
    {
        $productId = app(SnowflakeService::class)->nextId();
        $product = Product::factory()->make();
        $productData = $product->toArray();
        $productData['id'] = $productId;
        if (! isset($productData['status'])) {
            $productData['status'] = 'active';
        }
        unset($productData['product_variants'], $productData['product_translations']);

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => $productData,
                'source_node' => 'node2',
                // 不设置 timestamp，应该使用默认值
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(1, $result['success']);
        $this->assertEquals(0, $result['failed']);
    }

    /**
     * 测试：receiveBatchSync 处理模型创建失败（无效的模型类型）
     */
    public function test_receive_batch_sync_handles_model_creation_failure()
    {
        $productId = app(SnowflakeService::class)->nextId();
        
        // 使用无效的模型类型
        $batchData = [
            [
                'model_type' => 'NonExistentModel',
                'model_id' => $productId,
                'action' => 'created',
                'payload' => [
                    'id' => $productId,
                ],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        // 应该失败（模型不在同步列表中）
        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertStringContainsString('模型不在同步列表中', $result['results'][0]['error'] ?? '');
    }

    /**
     * 测试：receiveBatchSync 处理批量数据中部分失败
     */
    public function test_receive_batch_sync_handles_partial_failure_in_batch()
    {
        $productId1 = app(SnowflakeService::class)->nextId();
        $product1 = Product::factory()->make();
        $productData1 = $product1->toArray();
        $productData1['id'] = $productId1;
        if (! isset($productData1['status'])) {
            $productData1['status'] = 'active';
        }
        unset($productData1['product_variants'], $productData1['product_translations']);

        $productId2 = app(SnowflakeService::class)->nextId();

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId1,
                'action' => 'created',
                'payload' => $productData1,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'model_type' => 'InvalidModel',
                'model_id' => $productId2,
                'action' => 'created',
                'payload' => [],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        // 第一个成功，第二个失败
        $this->assertEquals(1, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertDatabaseCount('products', 1);
    }

    /**
     * 测试：receiveBatchSync 处理 updated_at 为 null 的情况
     */
    public function test_receive_batch_sync_handles_null_updated_at()
    {
        $product = Product::factory()->create();
        // 设置 updated_at 为 null（模拟新创建的记录）
        $product->updated_at = null;
        $product->save();

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $product->id,
                'action' => 'updated',
                'payload' => $product->toArray(),
                'source_node' => 'node2',
                'timestamp' => now()->subHour()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        // updated_at 为 null 时应该同步
        $this->assertEquals(1, $result['success']);
        $this->assertFalse($result['results'][0]['skipped'] ?? false);
    }

    /**
     * 测试：preparePayload 处理 deleted action
     */
    public function test_prepare_payload_for_deleted_action()
    {
        $product = Product::factory()->create();
        
        $reflection = new \ReflectionClass($this->service);
        $preparePayloadMethod = $reflection->getMethod('preparePayload');
        $preparePayloadMethod->setAccessible(true);

        $payload = $preparePayloadMethod->invoke($this->service, $product, 'deleted');

        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('deleted_at', $payload);
        $this->assertEquals($product->id, $payload['id']);
    }

    /**
     * 测试：preparePayload 处理时间戳规范化
     */
    public function test_prepare_payload_normalizes_timestamps()
    {
        $product = Product::factory()->create();
        
        $reflection = new \ReflectionClass($this->service);
        $preparePayloadMethod = $reflection->getMethod('preparePayload');
        $preparePayloadMethod->setAccessible(true);

        $payload = $preparePayloadMethod->invoke($this->service, $product, 'updated');

        // 验证时间戳是 ISO8601 格式字符串
        $this->assertIsString($payload['created_at']);
        $this->assertIsString($payload['updated_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $payload['created_at']);
    }

    /**
     * 测试：getTargetNodes 排除当前节点
     */
    public function test_get_target_nodes_excludes_current_node()
    {
        Config::set('sync.node', 'node1');
        Config::set('sync.remote_nodes', [
            'node1' => [
                'url' => 'https://node1.example.com',
                'api_key' => 'test-api-key-1',
                'timeout' => 600,
            ],
            'node2' => [
                'url' => 'https://node2.example.com',
                'api_key' => 'test-api-key-2',
                'timeout' => 600,
            ],
        ]);

        $reflection = new \ReflectionClass($this->service);
        $getTargetNodesMethod = $reflection->getMethod('getTargetNodes');
        $getTargetNodesMethod->setAccessible(true);

        $targetNodes = $getTargetNodesMethod->invoke($this->service);

        // 应该排除 node1（当前节点）
        $this->assertNotContains('node1', $targetNodes);
        $this->assertContains('node2', $targetNodes);
    }

    /**
     * 测试：shouldCreateSyncLog 对于 deleted action 总是返回 true
     */
    public function test_should_create_sync_log_always_true_for_deleted()
    {
        $product = Product::factory()->create();
        
        $reflection = new \ReflectionClass($this->service);
        $shouldCreateSyncLogMethod = $reflection->getMethod('shouldCreateSyncLog');
        $shouldCreateSyncLogMethod->setAccessible(true);

        // deleted action 应该总是返回 true
        $result = $shouldCreateSyncLogMethod->invoke(
            $this->service,
            Product::class,
            $product->id,
            'deleted',
            'node2',
            'any-hash'
        );

        $this->assertTrue($result);
    }

    /**
     * 测试：updateModel 处理空更新数据
     */
    public function test_update_existing_model_handles_empty_update_data()
    {
        $product = Product::factory()->create();
        $originalStatus = $product->status;

        $reflection = new \ReflectionClass($this->service);
        $updateModelMethod = $reflection->getMethod('updateModel');
        $updateModelMethod->setAccessible(true);

        // 只包含时间戳和ID的 payload
        $cleanPayload = [
            'id' => $product->id,
            'created_at' => $product->created_at->toIso8601String(),
            'updated_at' => $product->updated_at->toIso8601String(),
        ];

        $updatedModel = $updateModelMethod->invoke($this->service, Product::class, $product->id, $cleanPayload);

        $this->assertNotNull($updatedModel);
        $product->refresh();
        // 状态应该保持不变
        $this->assertEquals($originalStatus, $product->status);
    }

    /**
     * 测试：parseTimestampsInPayload 处理各种时间戳格式
     */
    public function test_parse_timestamps_in_payload_handles_various_formats()
    {
        $reflection = new \ReflectionClass($this->service);
        $parseTimestampsMethod = $reflection->getMethod('parseTimestampsInPayload');
        $parseTimestampsMethod->setAccessible(true);

        // 测试 ISO8601 字符串格式
        $payload1 = [
            'created_at' => '2024-01-01T00:00:00+00:00',
            'updated_at' => '2024-01-01T00:00:00+00:00',
        ];
        $parseTimestampsMethod->invokeArgs($this->service, [&$payload1]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payload1['created_at']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payload1['updated_at']);

        // 测试 Y-m-d H:i:s 格式
        $payload2 = [
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
        $parseTimestampsMethod->invokeArgs($this->service, [&$payload2]);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payload2['created_at']);
    }

    /**
     * 测试：createModel 处理创建失败的情况
     */
    public function test_create_new_model_handles_creation_failure()
    {
        $productId = app(SnowflakeService::class)->nextId();
        
        $reflection = new \ReflectionClass($this->service);
        $createModelMethod = $reflection->getMethod('createModel');
        $createModelMethod->setAccessible(true);

        // 缺少必填字段的 payload
        $cleanPayload = [
            'id' => $productId,
            // 缺少 slug（必填字段）
        ];

        $result = $createModelMethod->invoke($this->service, Product::class, $productId, $cleanPayload);

        // 应该返回 null（创建失败）
        $this->assertNull($result);
    }
}

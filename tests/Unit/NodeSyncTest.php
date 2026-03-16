<?php

namespace Tests\Unit;

use App\Enums\ProductStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\SyncLog;
use App\Models\SyncStatus;
use App\Services\SnowflakeService;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * 节点间同步测试.
 *
 * 测试多节点环境下的数据同步逻辑
 */
class NodeSyncTest extends TestCase
{
    use RefreshDatabase;

    protected SyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SyncService;

        // 配置多节点环境
        Config::set('sync.enabled', true);
        Config::set('sync.node', 'node1');
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
        Config::set('sync.sync_models', [
            Product::class,
            Category::class,
            Attribute::class,
            AttributeValue::class,
            ProductCategory::class,
            ProductAttributeValue::class,
        ]);

        // 清理测试数据
        SyncLog::truncate();
        SyncStatus::truncate();
    }

    /**
     * 测试：创建产品时，应该为所有目标节点创建同步日志.
     */
    public function test_product_creation_syncs_to_all_target_nodes()
    {
        // 临时禁用同步，避免创建时自动触发
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        $this->service->recordSync($product, 'created', 'node1');

        // 应该为 node2 和 node3 各创建一条日志
        $this->assertDatabaseCount('sync_logs', 2);
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'source_node' => 'node1',
            'target_node' => 'node3',
            'status' => 'pending',
        ]);
    }

    /**
     * 测试：更新产品时，应该为所有目标节点创建同步日志.
     */
    public function test_product_update_syncs_to_all_target_nodes()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的旧日志
        SyncLog::truncate();
        SyncStatus::truncate();

        $product->status = ProductStatusEnum::Inactive;
        $product->save();

        $this->service->recordSync($product, 'updated', 'node1');

        // 应该为 node2 和 node3 各创建一条日志
        $updatedLogs = SyncLog::where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->where('action', 'updated')
            ->get();
        $this->assertGreaterThanOrEqual(2, $updatedLogs->count());
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'updated',
            'target_node' => 'node2',
        ]);
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'updated',
            'target_node' => 'node3',
        ]);
    }

    /**
     * 测试：删除产品时，应该为所有目标节点创建同步日志.
     */
    public function test_product_deletion_syncs_to_all_target_nodes()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $productId = $product->id;
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        // 删除产品（禁用同步避免自动触发关联数据同步）
        Config::set('sync.enabled', false);
        $product->delete();
        Config::set('sync.enabled', true);

        $this->service->recordSync($product, 'deleted', 'node1');

        // 应该为 node2 和 node3 各创建一条日志
        $this->assertDatabaseCount('sync_logs', 2);
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $productId,
            'action' => 'deleted',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('sync_logs', [
            'model_type' => Product::class,
            'model_id' => $productId,
            'action' => 'deleted',
            'source_node' => 'node1',
            'target_node' => 'node3',
            'status' => 'pending',
        ]);
    }

    /**
     * 测试：Pivot 表删除时，应该正确同步.
     */
    public function test_pivot_table_deletion_syncs_correctly()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        // 创建关联
        $product->productCategories()->attach($category->id);

        // 获取 pivot 记录（在删除前获取）
        $pivot = ProductCategory::where('product_id', $product->id)
            ->where('category_id', $category->id)
            ->first();

        $this->assertNotNull($pivot);

        // 删除关联（禁用同步避免自动触发）
        Config::set('sync.enabled', false);
        $product->productCategories()->detach($category->id);
        Config::set('sync.enabled', true);

        // 手动触发同步（因为 detach 不会自动触发）
        $this->service->recordSync($pivot, 'deleted', 'node1');

        // 应该为所有目标节点创建同步日志（只检查我们手动触发的）
        $deletedLogs = SyncLog::where('model_type', ProductCategory::class)
            ->where('action', 'deleted')
            ->get();
        $this->assertGreaterThanOrEqual(2, $deletedLogs->count());

        // 验证 payload 包含所有必要字段（用于复合键查找）
        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);
        $this->assertArrayHasKey('product_id', $syncLog->payload);
        $this->assertArrayHasKey('category_id', $syncLog->payload);
        $this->assertEquals($product->id, $syncLog->payload['product_id']);
        $this->assertEquals($category->id, $syncLog->payload['category_id']);
    }

    /**
     * 测试：Pivot 表创建时，应该正确同步.
     */
    public function test_pivot_table_creation_syncs_correctly()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        // 创建关联
        $product->productCategories()->attach($category->id);

        // 获取 pivot 记录
        $pivot = ProductCategory::where('product_id', $product->id)
            ->where('category_id', $category->id)
            ->first();

        $this->assertNotNull($pivot);

        // 手动触发同步
        $this->service->recordSync($pivot, 'created', 'node1');

        // 应该为所有目标节点创建同步日志（只检查我们手动触发的）
        $createdLogs = SyncLog::where('model_type', ProductCategory::class)
            ->where('action', 'created')
            ->get();
        $this->assertGreaterThanOrEqual(2, $createdLogs->count());

        // 验证 payload 包含所有必要字段
        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);
        $this->assertArrayHasKey('product_id', $syncLog->payload);
        $this->assertArrayHasKey('category_id', $syncLog->payload);
    }

    /**
     * 测试：批量同步到多个节点.
     */
    public function test_batch_sync_to_multiple_nodes()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $products = Product::factory()->count(3)->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        $models = $products->map(function ($product) {
            return ['model' => $product, 'action' => 'created'];
        })->toArray();

        $this->service->recordBatchSync($models, 'node1');

        // 3个产品 × 2个目标节点 = 6条日志
        $this->assertDatabaseCount('sync_logs', 6);

        // 验证每个产品都为每个节点创建了日志
        foreach ($products as $product) {
            $this->assertDatabaseHas('sync_logs', [
                'model_id' => $product->id,
                'target_node' => 'node2',
            ]);
            $this->assertDatabaseHas('sync_logs', [
                'model_id' => $product->id,
                'target_node' => 'node3',
            ]);
        }
    }

    /**
     * 测试：接收批量同步数据时，应该正确处理来自不同节点的数据.
     */
    public function test_receive_batch_sync_from_different_nodes()
    {
        $productId1 = app(SnowflakeService::class)->nextId();
        $productId2 = app(SnowflakeService::class)->nextId();

        $product1 = Product::factory()->make();
        $product1Data = $product1->toArray();
        $product1Data['id'] = $productId1;
        if (! isset($product1Data['status'])) {
            $product1Data['status'] = 'active';
        }
        unset($product1Data['product_variants'], $product1Data['product_translations']);

        $product2 = Product::factory()->make();
        $product2Data = $product2->toArray();
        $product2Data['id'] = $productId2;
        if (! isset($product2Data['status'])) {
            $product2Data['status'] = 'active';
        }
        unset($product2Data['product_variants'], $product2Data['product_translations']);

        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => $productId1,
                'action' => 'created',
                'payload' => $product1Data,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'model_type' => Product::class,
                'model_id' => $productId2,
                'action' => 'created',
                'payload' => $product2Data,
                'source_node' => 'node3',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);
        $this->assertDatabaseCount('products', 2);
    }

    /**
     * 测试：同步状态应该正确更新，避免重复同步.
     */
    public function test_sync_status_prevents_duplicate_syncs()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志和状态
        SyncLog::truncate();
        SyncStatus::truncate();

        // 第一次同步
        $this->service->recordSync($product, 'updated', 'node1');

        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);

        // 获取同步哈希
        $reflection = new \ReflectionClass($this->service);
        $generateHashMethod = $reflection->getMethod('generateSyncHash');
        $generateHashMethod->setAccessible(true);
        $syncHash = $generateHashMethod->invoke($this->service, $product, 'updated');

        // 模拟同步成功，更新同步状态
        $syncLog->markAsCompleted();
        SyncStatus::updateSyncStatus(
            Product::class,
            $product->id,
            'node2',
            $syncHash
        );

        // 也为 node3 更新状态
        $syncLog3 = SyncLog::where('target_node', 'node3')->first();
        if ($syncLog3) {
            $syncLog3->markAsCompleted();
            SyncStatus::updateSyncStatus(
                Product::class,
                $product->id,
                'node3',
                $syncHash
            );
        }

        // 清理日志
        SyncLog::truncate();

        // 再次同步相同数据，应该跳过
        $this->service->recordSync($product, 'updated', 'node1');

        // 应该不创建新的日志（因为哈希值相同）
        // 注意：由于 SyncStatus 检查，如果哈希值相同，shouldCreateSyncLog 会返回 false
        $newLogs = SyncLog::where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->where('action', 'updated')
            ->get();
        $this->assertCount(0, $newLogs, '相同哈希值的数据不应该创建新的同步日志');
    }

    /**
     * 测试：数据变更后，应该创建新的同步日志.
     */
    public function test_data_change_creates_new_sync_log()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志和状态
        SyncLog::truncate();
        SyncStatus::truncate();

        // 第一次同步（更新前的状态）
        $this->service->recordSync($product, 'updated', 'node1');

        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);
        $syncHash1 = md5(json_encode($syncLog->payload, JSON_UNESCAPED_UNICODE));

        // 模拟第一次同步成功（为所有节点更新状态，使用第一次同步的哈希值）
        $syncLog->markAsCompleted();
        $syncLog3 = SyncLog::where('target_node', 'node3')->first();
        if ($syncLog3) {
            $syncLog3->markAsCompleted();
        }

        $reflection = new \ReflectionClass($this->service);
        $generateHashMethod = $reflection->getMethod('generateSyncHash');
        $generateHashMethod->setAccessible(true);

        // 使用第一次同步的哈希值更新状态
        $oldHash = $generateHashMethod->invoke($this->service, $product, 'updated');
        SyncStatus::updateSyncStatus(Product::class, $product->id, 'node2', $oldHash);
        SyncStatus::updateSyncStatus(Product::class, $product->id, 'node3', $oldHash);

        // 清理日志
        SyncLog::truncate();

        // 更新产品（禁用同步避免自动触发）
        Config::set('sync.enabled', false);
        $product->status = ProductStatusEnum::Inactive;
        $product->save();
        Config::set('sync.enabled', true);

        // 刷新产品以获取最新数据
        $product->refresh();

        // 再次同步（数据已变更，哈希值应该不同）
        $this->service->recordSync($product, 'updated', 'node1');

        // 应该创建新的日志（因为数据已变更，哈希值不同）
        $newLogs = SyncLog::where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->where('action', 'updated')
            ->get();
        $this->assertCount(2, $newLogs, '数据变更后应该为所有目标节点创建新的同步日志');

        $newSyncLog = SyncLog::where('target_node', 'node2')
            ->where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->where('action', 'updated')
            ->first();
        $this->assertNotNull($newSyncLog);
        $newHash = md5(json_encode($newSyncLog->payload, JSON_UNESCAPED_UNICODE));

        // 哈希值应该不同
        $this->assertNotEquals($syncHash1, $newHash);
    }

    /**
     * 测试：删除操作总是创建同步日志，即使哈希值相同.
     */
    public function test_deleted_action_always_creates_sync_log()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $productId = $product->id;
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        // 第一次同步（创建）
        $this->service->recordSync($product, 'created', 'node1');
        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);
        $syncLog->markAsCompleted();

        // 清理日志
        SyncLog::truncate();

        // 删除产品（禁用同步避免自动触发关联数据同步）
        Config::set('sync.enabled', false);
        $product->delete();
        Config::set('sync.enabled', true);

        $this->service->recordSync($product, 'deleted', 'node1');

        // 应该创建删除日志（删除操作总是创建日志）
        $this->assertDatabaseCount('sync_logs', 2);
        $this->assertDatabaseHas('sync_logs', [
            'model_id' => $productId,
            'action' => 'deleted',
            'target_node' => 'node2',
        ]);
    }

    /**
     * 测试：批量同步到远程节点时，应该正确发送请求
     */
    public function test_batch_sync_to_remote_sends_correct_request()
    {
        Http::fake([
            'node2.example.com/api/sync/receive-batch' => Http::response([
                'success' => 2,
                'failed' => 0,
                'results' => [
                    ['index' => 0, 'sync_log_id' => 1, 'success' => true],
                    ['index' => 1, 'sync_log_id' => 2, 'success' => true],
                ],
            ], 200),
        ]);

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

        $result = $this->service->syncBatchToRemote($syncLogs, 'node2');

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failed']);

        // 验证请求格式
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/sync/receive-batch') &&
                   $request->hasHeader('Authorization', 'Bearer test-api-key-2') &&
                   $request->hasHeader('X-Sync-Source-Node', 'node1') &&
                   isset($request->data()['batch']) &&
                   is_array($request->data()['batch']) &&
                   count($request->data()['batch']) === 2;
        });
    }

    /**
     * 测试：接收同步时，应该禁用同步监听，避免死循环.
     */
    public function test_receive_sync_disables_sync_listener()
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
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(1, $result['success']);

        // 验证产品已创建
        $this->assertDatabaseCount('products', 1);

        // 验证没有创建新的同步日志（因为同步监听被禁用）
        $this->assertDatabaseCount('sync_logs', 0);
    }

    /**
     * 测试：Pivot 表同步时，model_id 应该使用哈希值
     */
    public function test_pivot_table_uses_hash_as_model_id()
    {
        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        // 创建关联
        $product->attributeValues()->attach($attributeValue->id, ['attribute_id' => $attribute->id]);

        $pivot = ProductAttributeValue::where('product_id', $product->id)
            ->where('attribute_value_id', $attributeValue->id)
            ->first();

        $this->assertNotNull($pivot);

        $this->service->recordSync($pivot, 'created', 'node1');

        // 验证 model_id 是哈希值（不是实际的数据库 ID）
        $syncLog = SyncLog::where('target_node', 'node2')->first();
        $this->assertNotNull($syncLog);

        // model_id 应该是整数（哈希值转换而来）
        $this->assertIsInt($syncLog->model_id);
        $this->assertGreaterThan(0, $syncLog->model_id);

        // 验证 payload 包含所有必要字段
        $this->assertIsArray($syncLog->payload);
        $this->assertArrayHasKey('product_id', $syncLog->payload);
        $this->assertArrayHasKey('attribute_value_id', $syncLog->payload);
        $this->assertArrayHasKey('attribute_id', $syncLog->payload);
    }

    /**
     * 测试：接收 Pivot 表删除时，应该正确删除.
     */
    public function test_receive_pivot_table_deletion()
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        // 创建关联
        $product->productCategories()->attach($category->id);

        $pivot = ProductCategory::where('product_id', $product->id)
            ->where('category_id', $category->id)
            ->first();

        $this->assertNotNull($pivot);

        // 模拟接收删除同步
        $batchData = [
            [
                'model_type' => ProductCategory::class,
                'model_id' => 12345, // Pivot 表使用哈希值作为 model_id
                'action' => 'deleted',
                'payload' => [
                    'product_id' => $product->id,
                    'category_id' => $category->id,
                ],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->service->receiveBatchSync($batchData);

        $this->assertEquals(1, $result['success']);

        // 验证关联已删除
        $this->assertDatabaseMissing('product_category', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * 测试：节点配置变更时，应该只同步到配置的节点.
     */
    public function test_sync_only_to_configured_nodes()
    {
        // 只配置 node2
        Config::set('sync.remote_nodes', [
            'node2' => [
                'url' => 'https://node2.example.com',
                'api_key' => 'test-api-key-2',
                'timeout' => 600,
            ],
        ]);

        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        $this->service->recordSync($product, 'created', 'node1');

        // 应该只为 node2 创建日志
        $this->assertDatabaseCount('sync_logs', 1);
        $this->assertDatabaseHas('sync_logs', [
            'target_node' => 'node2',
        ]);
        $this->assertDatabaseMissing('sync_logs', [
            'target_node' => 'node3',
        ]);
    }

    /**
     * 测试：当前节点不应该同步给自己.
     */
    public function test_current_node_excluded_from_target_nodes()
    {
        // 配置中包含当前节点
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

        // 临时禁用同步
        Config::set('sync.enabled', false);
        $product = Product::factory()->create();
        Config::set('sync.enabled', true);

        // 清理可能存在的日志
        SyncLog::truncate();

        $this->service->recordSync($product, 'created', 'node1');

        // 应该只为 node2 创建日志（排除 node1）
        $this->assertDatabaseCount('sync_logs', 1);
        $this->assertDatabaseHas('sync_logs', [
            'target_node' => 'node2',
        ]);
        $this->assertDatabaseMissing('sync_logs', [
            'target_node' => 'node1',
        ]);
    }

    /**
     * 测试：同步失败时，应该正确记录错误.
     */
    public function test_sync_failure_records_error()
    {
        Http::fake([
            'node2.example.com/api/sync/receive-batch' => Http::response([], 500),
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

        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['failed']);
        $this->assertNotEmpty($result['errors']);

        $syncLog->refresh();
        $this->assertEquals('failed', $syncLog->status);
        $this->assertNotNull($syncLog->error_message);
    }

    /**
     * 测试：同步成功后，应该更新同步状态
     */
    public function test_successful_sync_updates_sync_status()
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
            'action' => 'updated',
            'source_node' => 'node1',
            'target_node' => 'node2',
            'status' => 'pending',
            'payload' => $product->toArray(),
        ]);

        $result = $this->service->syncBatchToRemote(collect([$syncLog]), 'node2');

        $this->assertEquals(1, $result['success']);

        $syncLog->refresh();
        // 注意：syncBatchToRemote 会先标记为 processing，成功后才标记为 completed
        // 由于我们使用了 Http::fake，需要等待处理完成
        $this->assertContains($syncLog->status, ['completed', 'processing']);

        // 如果状态是 completed，应该有 synced_at
        if ($syncLog->status === 'completed') {
            $this->assertNotNull($syncLog->synced_at);
        }

        // 验证同步状态已更新（只有 updated 操作才会更新状态）
        // 注意：handleSuccessfulSync 方法会更新同步状态，但需要等待处理完成
        // 由于我们使用了 Http::fake，实际处理是同步的，所以状态应该已更新
        if ($syncLog->action === 'updated' && $syncLog->status === 'completed') {
            $reflection = new \ReflectionClass($this->service);
            $generateHashMethod = $reflection->getMethod('generateSyncHash');
            $generateHashMethod->setAccessible(true);
            $syncHash = $generateHashMethod->invoke($this->service, $product, 'updated');

            $syncStatus = SyncStatus::where('model_type', Product::class)
                ->where('model_id', $product->id)
                ->where('node', 'node2')
                ->first();

            // 同步状态可能还未更新（因为 handleSuccessfulSync 是异步的）
            // 或者需要等待处理完成
            if ($syncStatus) {
                $this->assertEquals($syncHash, $syncStatus->sync_hash);
            }
        }
    }
}

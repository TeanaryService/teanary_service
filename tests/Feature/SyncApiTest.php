<?php

namespace Tests\Feature;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use App\Services\SnowflakeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 配置同步服务
        Config::set('sync.enabled', true);
        Config::set('sync.node', 'node1');
        Config::set('sync.remote_nodes', [
            'node2' => [
                'url' => 'https://node2.example.com',
                'api_key' => 'test-api-key-node2',
                'timeout' => 600,
            ],
            'node3' => [
                'url' => 'https://node3.example.com',
                'api_key' => 'test-api-key-node3',
                'timeout' => 600,
            ],
        ]);
        Config::set('sync.sync_models', [
            Product::class,
        ]);
    }

    /**
     * 测试：node2 成功发送同步数据到 node1（创建产品）
     */
    public function test_node2_sends_sync_data_to_node1_creates_product()
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

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '批量同步完成',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'success',
                    'failed',
                    'results',
                ],
            ]);

        // 检查响应数据
        $responseData = $response->json();
        
        // 如果同步失败，输出详细信息
        if ($responseData['data']['failed'] > 0) {
            $this->fail('同步失败: ' . json_encode($responseData['data']['results']));
        }
        
        $this->assertEquals(1, $responseData['data']['success'], '同步应该成功: ' . json_encode($responseData['data']));
        $this->assertEquals(0, $responseData['data']['failed']);

        // 验证产品已创建（可能ID不同，但应该有一条记录）
        $this->assertDatabaseCount('products', 1);
        $createdProduct = Product::first();
        $this->assertNotNull($createdProduct, '产品应该已创建');
        $this->assertEquals($productData['slug'], $createdProduct->slug);
    }

    /**
     * 测试：node2 成功发送同步数据到 node1（更新产品）
     */
    public function test_node2_sends_sync_data_to_node1_updates_product()
    {
        // 先创建一个产品
        $product = Product::factory()->create();
        
        $updatedData = $product->toArray();
        $updatedData['status'] = ProductStatusEnum::Inactive->value;

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

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 验证产品已更新
        $product->refresh();
        $this->assertEquals(ProductStatusEnum::Inactive, $product->status);
    }

    /**
     * 测试：node2 成功发送同步数据到 node1（删除产品）
     */
    public function test_node2_sends_sync_data_to_node1_deletes_product()
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

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 验证产品已删除
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    /**
     * 测试：node2 发送批量同步数据到 node1
     */
    public function test_node2_sends_batch_sync_data_to_node1()
    {
        $productIds = [];
        $batchData = [];

        // 创建3个产品的同步数据
        for ($i = 0; $i < 3; $i++) {
            $productId = app(SnowflakeService::class)->nextId();
            $productIds[] = $productId;
            
            $product = Product::factory()->make();
            $productData = $product->toArray();
            $productData['id'] = $productId;
            
            // 确保包含所有必需的字段
            if (! isset($productData['status'])) {
                $productData['status'] = 'active';
            }
            
            // 移除可能干扰的字段
            unset($productData['product_variants'], $productData['product_translations']);

            $batchData[] = [
                'model_type' => Product::class,
                'model_id' => $productId,
                'action' => 'created',
                'payload' => $productData,
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 验证所有产品都已创建
        $this->assertDatabaseCount('products', 3);
        
        // 验证所有产品都已创建（检查是否有3条记录）
        $products = Product::all();
        $this->assertCount(3, $products);
    }

    /**
     * 测试：无效的 API Key 被拒绝
     */
    public function test_invalid_api_key_is_rejected()
    {
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => app(SnowflakeService::class)->nextId(),
                'action' => 'created',
                'payload' => ['id' => 1],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer invalid-api-key',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => '无效的 API Key',
            ]);
    }

    /**
     * 测试：无效的来源节点被拒绝
     */
    public function test_invalid_source_node_is_rejected()
    {
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => app(SnowflakeService::class)->nextId(),
                'action' => 'created',
                'payload' => ['id' => 1],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'invalid-node',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => '无效的来源节点',
            ]);
    }

    /**
     * 测试：缺少来源节点 header 被拒绝
     */
    public function test_missing_source_node_header_is_rejected()
    {
        $batchData = [
            [
                'model_type' => Product::class,
                'model_id' => app(SnowflakeService::class)->nextId(),
                'action' => 'created',
                'payload' => ['id' => 1],
                'source_node' => 'node2',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => '无效的来源节点',
            ]);
    }

    /**
     * 测试：无效的请求数据被拒绝
     */
    public function test_invalid_request_data_is_rejected()
    {
        // 缺少必需的字段
        $batchData = [
            [
                'model_id' => app(SnowflakeService::class)->nextId(),
                'action' => 'created',
                // 缺少 model_type 和 payload
            ],
        ];

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => '请求数据验证失败',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * 测试：同步后缓存被清除
     */
    public function test_cache_is_cleared_after_sync()
    {
        Cache::put('test_key', 'test_value', 60);
        $this->assertNotNull(Cache::get('test_key'));

        $productId = app(SnowflakeService::class)->nextId();
        $product = Product::factory()->make();
        
        // 构建 payload，确保包含所有必需的字段
        $productData = [
            'id' => $productId,
            'slug' => $product->slug,
            'status' => $product->status->value ?? ProductStatusEnum::Active->value,
            'source_url' => $product->source_url ?? null,
            'translation_status' => $product->translation_status->value ?? null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

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

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200);

        // 验证缓存已被清除
        $this->assertNull(Cache::get('test_key'));
    }

    /**
     * 测试：如果本地数据更新，则跳过同步
     */
    public function test_sync_is_skipped_when_local_data_is_newer()
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

        $response = $this->postJson('/api/sync/receive-batch', [
            'batch' => $batchData,
            'source_node' => 'node2',
            'timestamp' => now()->toIso8601String(),
        ], [
            'Authorization' => 'Bearer test-api-key-node2',
            'X-Sync-Source-Node' => 'node2',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 验证结果中包含 skipped 标记
        $responseData = $response->json();
        $this->assertTrue($responseData['data']['results'][0]['skipped'] ?? false);
    }
}

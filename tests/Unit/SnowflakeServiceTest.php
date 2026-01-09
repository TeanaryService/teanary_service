<?php

namespace Tests\Unit;

use App\Services\SnowflakeService;
use Tests\TestCase;

class SnowflakeServiceTest extends TestCase
{
    /**
     * 测试：生成唯一的雪花ID
     */
    public function test_generates_unique_ids()
    {
        $service = new SnowflakeService(1);
        $ids = [];

        // 生成1000个ID，确保都是唯一的
        for ($i = 0; $i < 1000; $i++) {
            $id = $service->nextId();
            $this->assertNotContains($id, $ids, '生成的ID应该是唯一的');
            $ids[] = $id;
        }

        $this->assertCount(1000, array_unique($ids));
    }

    /**
     * 测试：不同机器ID生成不同的ID
     */
    public function test_different_machine_ids_generate_different_ids()
    {
        $service1 = new SnowflakeService(1);
        $service2 = new SnowflakeService(2);

        $id1 = $service1->nextId();
        $id2 = $service2->nextId();

        $this->assertNotEquals($id1, $id2);
    }

    /**
     * 测试：机器ID边界值
     */
    public function test_machine_id_boundaries()
    {
        // 最小机器ID
        $service1 = new SnowflakeService(0);
        $id1 = $service1->nextId();
        $this->assertIsInt($id1);
        $this->assertGreaterThan(0, $id1);

        // 最大机器ID
        $service2 = new SnowflakeService(1023);
        $id2 = $service2->nextId();
        $this->assertIsInt($id2);
        $this->assertGreaterThan(0, $id2);
    }

    /**
     * 测试：无效的机器ID抛出异常
     */
    public function test_invalid_machine_id_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('机器ID必须在0到1023之间');

        new SnowflakeService(1024);
    }

    /**
     * 测试：负数机器ID抛出异常
     */
    public function test_negative_machine_id_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('机器ID必须在0到1023之间');

        new SnowflakeService(-1);
    }

    /**
     * 测试：解析时间戳
     */
    public function test_parse_timestamp()
    {
        $service = new SnowflakeService(1);
        $id = $service->nextId();

        $timestamp = SnowflakeService::parseTimestamp($id);

        // 时间戳应该在合理范围内（2024年之后）
        $this->assertGreaterThan(1704067200000, $timestamp);
        $this->assertLessThan(time() * 1000 + 1000, $timestamp);
    }

    /**
     * 测试：解析机器ID
     */
    public function test_parse_machine_id()
    {
        $machineId = 42;
        $service = new SnowflakeService($machineId);
        $id = $service->nextId();

        $parsedMachineId = SnowflakeService::parseMachineId($id);

        $this->assertEquals($machineId, $parsedMachineId);
    }

    /**
     * 测试：解析序列号
     */
    public function test_parse_sequence()
    {
        $service = new SnowflakeService(1);
        $ids = [];

        // 生成多个ID，检查序列号递增
        for ($i = 0; $i < 10; $i++) {
            $id = $service->nextId();
            $sequence = SnowflakeService::parseSequence($id);
            $ids[] = ['id' => $id, 'sequence' => $sequence];
        }

        // 验证序列号在合理范围内
        foreach ($ids as $item) {
            $this->assertGreaterThanOrEqual(0, $item['sequence']);
            $this->assertLessThanOrEqual(4095, $item['sequence']);
        }
    }

    /**
     * 测试：同一毫秒内序列号递增
     */
    public function test_sequence_increments_within_same_millisecond()
    {
        $service = new SnowflakeService(1);
        $sequences = [];

        // 快速生成多个ID（可能在同一毫秒内）
        for ($i = 0; $i < 100; $i++) {
            $id = $service->nextId();
            $sequences[] = SnowflakeService::parseSequence($id);
        }

        // 验证序列号是递增的（或重置）
        $hasIncrement = false;
        for ($i = 1; $i < count($sequences); $i++) {
            if ($sequences[$i] > $sequences[$i - 1] || $sequences[$i] === 0) {
                $hasIncrement = true;
                break;
            }
        }

        $this->assertTrue($hasIncrement || count(array_unique($sequences)) > 1);
    }

    /**
     * 测试：ID是64位整数
     */
    public function test_id_is_64_bit_integer()
    {
        $service = new SnowflakeService(1);
        $id = $service->nextId();

        // 验证是整数
        $this->assertIsInt($id);

        // 验证是正数
        $this->assertGreaterThan(0, $id);

        // 验证不超过64位整数最大值（PHP中int的最大值）
        $this->assertLessThanOrEqual(PHP_INT_MAX, $id);
    }

    /**
     * 测试：使用配置文件获取机器ID
     */
    public function test_uses_config_machine_id()
    {
        // 设置配置（模拟从 .env 读取）
        config(['snowflake.machine_id' => 100]);

        $service = new SnowflakeService();
        $id = $service->nextId();

        $parsedMachineId = SnowflakeService::parseMachineId($id);
        $this->assertEquals(100, $parsedMachineId);

        // 清理配置
        config(['snowflake.machine_id' => null]);
    }

    /**
     * 测试：大量ID生成性能
     */
    public function test_generates_large_number_of_ids()
    {
        $service = new SnowflakeService(1);
        $startTime = microtime(true);

        // 生成10000个ID
        for ($i = 0; $i < 10000; $i++) {
            $id = $service->nextId();
            $this->assertIsInt($id);
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 应该在1秒内完成（性能测试）
        $this->assertLessThan(1.0, $duration, '生成10000个ID应该在1秒内完成');
    }

    /**
     * 测试：ID的时间戳部分正确
     */
    public function test_id_timestamp_component_is_correct()
    {
        $service = new SnowflakeService(1);
        $beforeTime = (int) (microtime(true) * 1000);
        $id = $service->nextId();
        $afterTime = (int) (microtime(true) * 1000);

        $parsedTimestamp = SnowflakeService::parseTimestamp($id);

        // 解析出的时间戳应该在生成前后之间
        $this->assertGreaterThanOrEqual($beforeTime, $parsedTimestamp);
        $this->assertLessThanOrEqual($afterTime, $parsedTimestamp);
    }

    /**
     * 测试：序列号溢出处理
     */
    public function test_sequence_overflow_handling()
    {
        $service = new SnowflakeService(1);

        // 在同一毫秒内生成超过4096个ID（序列号最大值+1）
        // 这应该触发等待下一毫秒的逻辑
        $ids = [];
        $startTime = microtime(true);

        for ($i = 0; $i < 5000; $i++) {
            $id = $service->nextId();
            $ids[] = $id;
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 验证所有ID都是唯一的
        $this->assertCount(5000, array_unique($ids));

        // 验证序列号都在有效范围内
        foreach ($ids as $id) {
            $sequence = SnowflakeService::parseSequence($id);
            $this->assertGreaterThanOrEqual(0, $sequence);
            $this->assertLessThanOrEqual(4095, $sequence);
        }
    }

    /**
     * 测试：不同机器ID生成的ID不冲突
     */
    public function test_ids_from_different_machines_do_not_collide()
    {
        $service1 = new SnowflakeService(1);
        $service2 = new SnowflakeService(500);
        $service3 = new SnowflakeService(1023);

        $ids1 = [];
        $ids2 = [];
        $ids3 = [];

        // 每个服务生成100个ID
        for ($i = 0; $i < 100; $i++) {
            $ids1[] = $service1->nextId();
            $ids2[] = $service2->nextId();
            $ids3[] = $service3->nextId();
        }

        // 验证不同机器的ID不冲突
        $allIds = array_merge($ids1, $ids2, $ids3);
        $this->assertCount(300, array_unique($allIds));

        // 验证每个机器的ID都有正确的机器ID
        foreach ($ids1 as $id) {
            $this->assertEquals(1, SnowflakeService::parseMachineId($id));
        }
        foreach ($ids2 as $id) {
            $this->assertEquals(500, SnowflakeService::parseMachineId($id));
        }
        foreach ($ids3 as $id) {
            $this->assertEquals(1023, SnowflakeService::parseMachineId($id));
        }
    }
}

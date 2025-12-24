<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RequestQueryCacheService;

class RequestQueryCacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 清理静态缓存
        $reflection = new \ReflectionClass(RequestQueryCacheService::class);
        $property = $reflection->getProperty('cache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function test_remember_caches_result(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        $callCount = 0;

        $callback = function () use (&$callCount, $value) {
            $callCount++;
            return $value;
        };

        $result1 = RequestQueryCacheService::remember($key, $callback);
        $result2 = RequestQueryCacheService::remember($key, $callback);

        $this->assertEquals($value, $result1);
        $this->assertEquals($value, $result2);
        $this->assertEquals(1, $callCount); // 回调应该只执行一次
    }

    public function test_remember_returns_different_values_for_different_keys(): void
    {
        $value1 = RequestQueryCacheService::remember('key1', fn() => 'value1');
        $value2 = RequestQueryCacheService::remember('key2', fn() => 'value2');

        $this->assertEquals('value1', $value1);
        $this->assertEquals('value2', $value2);
    }

    public function test_remember_handles_closure_returning_null(): void
    {
        $result = RequestQueryCacheService::remember('null_key', fn() => null);

        $this->assertNull($result);
    }

    public function test_remember_handles_closure_returning_array(): void
    {
        $data = ['key' => 'value', 'number' => 123];
        $result = RequestQueryCacheService::remember('array_key', fn() => $data);

        $this->assertEquals($data, $result);
    }

    public function test_remember_handles_closure_returning_object(): void
    {
        $object = (object) ['property' => 'value'];
        $result = RequestQueryCacheService::remember('object_key', fn() => $object);

        $this->assertEquals($object, $result);
    }
}


<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 清除缓存
        \Illuminate\Support\Facades\Cache::flush();
        
        // 清除事件监听器（如果需要）
        // \Illuminate\Support\Facades\Event::fake();
    }

    /**
     * 清理测试环境
     */
    protected function tearDown(): void
    {
        // 清理测试数据
        \Illuminate\Support\Facades\Cache::flush();
        
        parent::tearDown();
    }
}


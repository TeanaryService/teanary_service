<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * 基础功能测试示例
     *
     * @return void
     */
    public function test_basic_feature()
    {
        $response = $this->get('/');

        // 根据实际路由，这里可能是重定向或200状态
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}


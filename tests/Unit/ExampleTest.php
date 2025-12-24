<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * 基础单元测试示例
     *
     * @return void
     */
    public function test_basic_assertion()
    {
        $this->assertTrue(true);
    }

    /**
     * 测试数组操作
     */
    public function test_array_operations()
    {
        $array = [1, 2, 3];
        
        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
        $this->assertEquals([1, 2, 3], $array);
    }
}


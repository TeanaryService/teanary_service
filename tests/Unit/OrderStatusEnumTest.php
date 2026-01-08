<?php

namespace Tests\Unit;

use App\Enums\OrderStatusEnum;
use Tests\TestCase;

class OrderStatusEnumTest extends TestCase
{
    /**
     * Test the label method returns the correct localized string.
     */
    public function test_label_returns_correct_string()
    {
        // Mock the __() helper function for testing purposes
        // In a real Laravel application, this would use actual translation files.
        // For unit testing an enum, we can simulate the return.
        $this->app->instance('translator', new class
        {
            public function get($key)
            {
                $map = [
                    'orders.status.pending' => '待处理',
                    'orders.status.paid' => '已支付',
                    'orders.status.shipped' => '已发货',
                    'orders.status.completed' => '已完成',
                    'orders.status.cancelled' => '已取消',
                    'orders.status.after_sale' => '售后处理中',
                    'orders.status.after_sale_done' => '售后完成',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('待处理', OrderStatusEnum::Pending->label());
        $this->assertEquals('已支付', OrderStatusEnum::Paid->label());
        $this->assertEquals('已发货', OrderStatusEnum::Shipped->label());
        $this->assertEquals('已完成', OrderStatusEnum::Completed->label());
        $this->assertEquals('已取消', OrderStatusEnum::Cancelled->label());
        $this->assertEquals('售后处理中', OrderStatusEnum::AfterSale->label());
        $this->assertEquals('售后完成', OrderStatusEnum::AfterSaleCompleted->label());
    }

    /**
     * Test the default method returns the correct default status.
     */
    public function test_default_returns_pending()
    {
        $this->assertEquals(OrderStatusEnum::Pending, OrderStatusEnum::default());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'pending',
            'paid',
            'shipped',
            'completed',
            'cancelled',
            'after_sale',
            'after_sale_done',
        ];
        $this->assertEquals($expectedValues, OrderStatusEnum::values());
    }

    /**
     * Test the options method returns all enum options (value => label).
     */
    public function test_options_returns_all_enum_options()
    {
        // Mock the __() helper function as above
        $this->app->instance('translator', new class
        {
            public function get($key)
            {
                $map = [
                    'orders.status.pending' => '待处理',
                    'orders.status.paid' => '已支付',
                    'orders.status.shipped' => '已发货',
                    'orders.status.completed' => '已完成',
                    'orders.status.cancelled' => '已取消',
                    'orders.status.after_sale' => '售后处理中',
                    'orders.status.after_sale_done' => '售后完成',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'pending' => '待处理',
            'paid' => '已支付',
            'shipped' => '已发货',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'after_sale' => '售后处理中',
            'after_sale_done' => '售后完成',
        ];
        $this->assertEquals($expectedOptions, OrderStatusEnum::options());
    }

    /**
     * Test the canBeCancelled method.
     */
    public function test_can_be_cancelled()
    {
        $this->assertTrue(OrderStatusEnum::Pending->canBeCancelled());
        $this->assertTrue(OrderStatusEnum::Paid->canBeCancelled());
        $this->assertFalse(OrderStatusEnum::Shipped->canBeCancelled());
        $this->assertFalse(OrderStatusEnum::Completed->canBeCancelled());
        $this->assertFalse(OrderStatusEnum::Cancelled->canBeCancelled());
        $this->assertFalse(OrderStatusEnum::AfterSale->canBeCancelled());
        $this->assertFalse(OrderStatusEnum::AfterSaleCompleted->canBeCancelled());
    }

    /**
     * Test the canBePaid method.
     */
    public function test_can_be_paid()
    {
        $this->assertTrue(OrderStatusEnum::Pending->canBePaid());
        $this->assertFalse(OrderStatusEnum::Paid->canBePaid());
        $this->assertFalse(OrderStatusEnum::Shipped->canBePaid());
        $this->assertFalse(OrderStatusEnum::Completed->canBePaid());
        $this->assertFalse(OrderStatusEnum::Cancelled->canBePaid());
        $this->assertFalse(OrderStatusEnum::AfterSale->canBePaid());
        $this->assertFalse(OrderStatusEnum::AfterSaleCompleted->canBePaid());
    }

    /**
     * Test the canRequestAfterSale method.
     */
    public function test_can_request_after_sale()
    {
        $this->assertFalse(OrderStatusEnum::Pending->canRequestAfterSale());
        $this->assertFalse(OrderStatusEnum::Paid->canRequestAfterSale());
        $this->assertTrue(OrderStatusEnum::Shipped->canRequestAfterSale());
        $this->assertTrue(OrderStatusEnum::Completed->canRequestAfterSale());
        $this->assertFalse(OrderStatusEnum::Cancelled->canRequestAfterSale());
        $this->assertFalse(OrderStatusEnum::AfterSale->canRequestAfterSale());
        $this->assertFalse(OrderStatusEnum::AfterSaleCompleted->canRequestAfterSale());
    }

    /**
     * Test the isAfterSaleProcessing method.
     */
    public function test_is_after_sale_processing()
    {
        $this->assertFalse(OrderStatusEnum::Pending->isAfterSaleProcessing());
        $this->assertFalse(OrderStatusEnum::Paid->isAfterSaleProcessing());
        $this->assertFalse(OrderStatusEnum::Shipped->isAfterSaleProcessing());
        $this->assertFalse(OrderStatusEnum::Completed->isAfterSaleProcessing());
        $this->assertFalse(OrderStatusEnum::Cancelled->isAfterSaleProcessing());
        $this->assertTrue(OrderStatusEnum::AfterSale->isAfterSaleProcessing());
        $this->assertFalse(OrderStatusEnum::AfterSaleCompleted->isAfterSaleProcessing());
    }

    /**
     * Test the isAfterSaleCompleted method.
     */
    public function test_is_after_sale_completed()
    {
        $this->assertFalse(OrderStatusEnum::Pending->isAfterSaleCompleted());
        $this->assertFalse(OrderStatusEnum::Paid->isAfterSaleCompleted());
        $this->assertFalse(OrderStatusEnum::Shipped->isAfterSaleCompleted());
        $this->assertFalse(OrderStatusEnum::Completed->isAfterSaleCompleted());
        $this->assertFalse(OrderStatusEnum::Cancelled->isAfterSaleCompleted());
        $this->assertFalse(OrderStatusEnum::AfterSale->isAfterSaleCompleted());
        $this->assertTrue(OrderStatusEnum::AfterSaleCompleted->isAfterSaleCompleted());
    }
}

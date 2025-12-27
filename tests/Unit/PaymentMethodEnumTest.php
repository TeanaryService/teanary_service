<?php

namespace Tests\Unit;

use App\Enums\PaymentMethodEnum;
use Tests\TestCase;

class PaymentMethodEnumTest extends TestCase
{
    /**
     * Test the label method returns the correct localized string.
     */
    public function test_label_returns_correct_string()
    {
        // Mock the __() helper function for testing purposes
        $this->app->instance('translator', new class
        {
            public function get($key)
            {
                $map = [
                    'payment.method.paypal' => '贝宝',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('贝宝', PaymentMethodEnum::PAYPAL->label());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'paypal',
        ];
        $this->assertEquals($expectedValues, PaymentMethodEnum::values());
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
                    'payment.method.paypal' => '贝宝',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'paypal' => '贝宝',
        ];
        $this->assertEquals($expectedOptions, PaymentMethodEnum::options());
    }

    /**
     * Test the apiParams method returns correct parameters.
     */
    public function test_api_params()
    {
        $params = PaymentMethodEnum::PAYPAL->apiParams();

        $this->assertIsArray($params);
        $this->assertArrayHasKey('sandBox', $params);
        $this->assertArrayHasKey('prod', $params);

        // Test sandBox parameters
        $this->assertArrayHasKey('client_id', $params['sandBox']);
        $this->assertArrayHasKey('secret', $params['sandBox']);
        $this->assertEquals('AaA1tR34wASiDfb7XWN1ffmwAVqSS4SLwHkHNt9w6U83tbXuCvzeyS3YB0yYi1MXxQwnFJXO6MsirsNR', $params['sandBox']['client_id']);
        $this->assertEquals('ENyFjPSaVzcMxb_A1n1v3nd7qRFWzfetYyh35YrnalU9RgJrqLQNH6tXx8L4a1rv4wRumODq6c7i8MEn', $params['sandBox']['secret']);

        // Test prod parameters
        $this->assertArrayHasKey('client_id', $params['prod']);
        $this->assertArrayHasKey('secret', $params['prod']);
        $this->assertEquals('AfbQnopEyt6omClqh1g97ajEHHYUiYBx6AyPkEkMVXRiK_oZp5p2mkqFhT84o3Xnf4uh3TYUWVJjZ-vd', $params['prod']['client_id']);
        $this->assertEquals('EKg2ti5svZqAbP2NBSGVEH4rKvpvVQnB1zkgOztqhXCKAlkuJMbjw0USeankeRFfK-W8HvlbHl_hTYJ3', $params['prod']['secret']);
    }

    /**
     * Test the random method returns a PaymentMethodEnum instance.
     */
    public function test_random_method_returns_enum_instance()
    {
        $randomEnum = PaymentMethodEnum::random();
        $this->assertInstanceOf(PaymentMethodEnum::class, $randomEnum);
        $this->assertContains($randomEnum, PaymentMethodEnum::cases());
    }

    /**
     * Test the fromValue method returns the correct enum instance for a valid value.
     */
    public function test_from_value_returns_correct_enum()
    {
        $enum = PaymentMethodEnum::fromValue('paypal');
        $this->assertInstanceOf(PaymentMethodEnum::class, $enum);
        $this->assertEquals(PaymentMethodEnum::PAYPAL, $enum);
    }

    /**
     * Test the fromValue method returns null for an invalid value.
     */
    public function test_from_value_returns_null_for_invalid_value()
    {
        $enum = PaymentMethodEnum::fromValue('invalid_method');
        $this->assertNull($enum);
    }
}

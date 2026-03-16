<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Models\Currency;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_no' => 'ORDER-'.fake()->unique()->numerify('########'),
            'currency_id' => Currency::factory(),
            'payment_method' => PaymentMethodEnum::PAYPAL,
            'shipping_method' => ShippingMethodEnum::SF_INTERNATIONAL,
            'shipping_address_id' => Address::factory(),
            'billing_address_id' => null,
            'total' => fake()->randomFloat(2, 10, 1000),
            'shipping_fee' => fake()->randomFloat(2, 0, 50),
            'status' => OrderStatusEnum::Pending,
        ];
    }
}

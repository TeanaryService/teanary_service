<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
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
            'user_id' => \App\Models\User::factory(),
            'order_no' => 'ORDER-'.fake()->unique()->numerify('########'),
            'currency_id' => \App\Models\Currency::factory(),
            'payment_method' => \App\Enums\PaymentMethodEnum::PAYPAL,
            'shipping_method' => \App\Enums\ShippingMethodEnum::SF_INTERNATIONAL,
            'shipping_address_id' => \App\Models\Address::factory(),
            'billing_address_id' => null,
            'total' => fake()->randomFloat(2, 10, 1000),
            'shipping_fee' => fake()->randomFloat(2, 0, 50),
            'status' => \App\Enums\OrderStatusEnum::Pending,
        ];
    }
}

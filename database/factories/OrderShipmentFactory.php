<?php

namespace Database\Factories;

use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderShipment>
 */
class OrderShipmentFactory extends Factory
{
    protected $model = OrderShipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'shipping_method' => ShippingMethodEnum::SF_INTERNATIONAL,
            'tracking_number' => fake()->optional()->numerify('TRACK#########'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

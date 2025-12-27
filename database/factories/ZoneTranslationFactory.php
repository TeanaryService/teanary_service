<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ZoneTranslation>
 */
class ZoneTranslationFactory extends Factory
{
    protected $model = ZoneTranslation::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->city(),
        ];
    }
}

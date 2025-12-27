<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_zone_can_be_created_using_factory()
    {
        $zone = Zone::factory()->create();

        $this->assertNotNull($zone);
        $this->assertInstanceOf(Zone::class, $zone);
    }

    public function test_active_attribute_casting()
    {
        $zone = Zone::factory()->create(['active' => true]);

        $this->assertIsBool($zone->active);
        $this->assertTrue($zone->active);
    }

    public function test_country_relationship()
    {
        $zone = new Zone;
        $relation = $zone->country();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('country_id', $relation->getForeignKeyName());
    }

    public function test_addresses_relationship()
    {
        $zone = new Zone;
        $relation = $zone->addresses();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('zone_id', $relation->getForeignKeyName());
    }

    public function test_zone_translations_relationship()
    {
        $zone = new Zone;
        $relation = $zone->zoneTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('zone_id', $relation->getForeignKeyName());
    }

    public function test_get_cached_zones()
    {
        Cache::flush();
        $country = Country::factory()->create();
        $zone = Zone::factory()->create(['country_id' => $country->id]);
        $translation = ZoneTranslation::factory()->create(['zone_id' => $zone->id]);

        $zones = Zone::getCachedZones();

        $this->assertIsArray($zones);
        $this->assertCount(1, $zones);
        $this->assertArrayHasKey('id', $zones[0]);
        $this->assertArrayHasKey('translations', $zones[0]);
        $this->assertTrue(Cache::has('zones.with.translations'));
    }

    public function test_get_zones_by_country_and_language()
    {
        Cache::flush();
        $language = \App\Models\Language::factory()->create();
        $country = Country::factory()->create();
        $zone = Zone::factory()->create(['country_id' => $country->id]);
        $translation = ZoneTranslation::factory()->create([
            'zone_id' => $zone->id,
            'language_id' => $language->id,
            'name' => 'Test Zone',
        ]);

        $zones = Zone::getZonesByCountryAndLanguage($country->id, $language->id);

        $this->assertIsArray($zones);
        $this->assertCount(1, $zones);
        $this->assertArrayHasKey('id', $zones[0]);
        $this->assertArrayHasKey('name', $zones[0]);
    }
}

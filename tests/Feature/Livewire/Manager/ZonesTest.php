<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Zones;
use App\Models\Country;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use Tests\Feature\LivewireTestCase;

class ZonesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->country = Country::factory()->create();
    }

    public function test_zones_page_can_be_rendered()
    {
        $component = $this->livewire(Zones::class);
        $component->assertSuccessful();
    }

    public function test_zones_list_displays_zones()
    {
        $zone = Zone::factory()->create(['country_id' => $this->country->id]);

        $component = $this->livewire(Zones::class);

        $zones = $component->get('zones');
        $zoneIds = $zones->pluck('id')->toArray();
        $this->assertContains($zone->id, $zoneIds);
    }

    public function test_can_search_zones_by_name()
    {
        $zone1 = Zone::factory()->create(['country_id' => $this->country->id]);
        $zone2 = Zone::factory()->create(['country_id' => $this->country->id]);

        ZoneTranslation::factory()->create([
            'zone_id' => $zone1->id,
            'name' => '测试地区1',
        ]);
        ZoneTranslation::factory()->create([
            'zone_id' => $zone2->id,
            'name' => '其他地区',
        ]);

        $component = $this->livewire(Zones::class)
            ->set('search', '测试');

        $zones = $component->get('zones');
        $zoneIds = $zones->pluck('id')->toArray();
        $this->assertContains($zone1->id, $zoneIds);
        $this->assertNotContains($zone2->id, $zoneIds);
    }

    public function test_can_filter_zones_by_country()
    {
        $country2 = Country::factory()->create();
        $zone1 = Zone::factory()->create(['country_id' => $this->country->id]);
        $zone2 = Zone::factory()->create(['country_id' => $country2->id]);

        $component = $this->livewire(Zones::class)
            ->set('filterCountryId', $this->country->id);

        $zones = $component->get('zones');
        $zoneIds = $zones->pluck('id')->toArray();
        $this->assertContains($zone1->id, $zoneIds);
        $this->assertNotContains($zone2->id, $zoneIds);
    }

    public function test_can_filter_zones_by_active_status()
    {
        $activeZone = Zone::factory()->create([
            'country_id' => $this->country->id,
            'active' => true,
        ]);
        $inactiveZone = Zone::factory()->create([
            'country_id' => $this->country->id,
            'active' => false,
        ]);

        $component = $this->livewire(Zones::class)
            ->set('filterActive', '1');

        $zones = $component->get('zones');
        $zoneIds = $zones->pluck('id')->toArray();
        $this->assertContains($activeZone->id, $zoneIds);
        $this->assertNotContains($inactiveZone->id, $zoneIds);
    }

    public function test_can_delete_zone()
    {
        $zone = Zone::factory()->create(['country_id' => $this->country->id]);

        $component = $this->livewire(Zones::class)
            ->call('deleteZone', $zone->id);

        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Zones::class)
            ->set('search', 'test')
            ->set('filterCountryId', 1)
            ->set('filterActive', '1')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterCountryId', null)
            ->assertSet('filterActive', '')
            ->assertSet('filterTranslationStatus', []);
    }
}

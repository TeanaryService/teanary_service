<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_can_be_created_using_factory()
    {
        $country = Country::factory()->create();

        $this->assertNotNull($country);
        $this->assertInstanceOf(Country::class, $country);
    }

    public function test_active_attribute_casting()
    {
        $country = Country::factory()->create(['active' => true]);

        $this->assertIsBool($country->active);
        $this->assertTrue($country->active);
    }

    public function test_addresses_relationship()
    {
        $country = new Country;
        $relation = $country->addresses();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('country_id', $relation->getForeignKeyName());
    }

    public function test_country_translations_relationship()
    {
        $country = new Country;
        $relation = $country->countryTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('country_id', $relation->getForeignKeyName());
    }

    public function test_zones_relationship()
    {
        $country = new Country;
        $relation = $country->zones();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('country_id', $relation->getForeignKeyName());
    }

    public function test_get_cached_countries()
    {
        Cache::flush();
        $country = Country::factory()->create();
        $translation = CountryTranslation::factory()->create(['country_id' => $country->id]);

        $countries = Country::getCachedCountries();

        $this->assertIsArray($countries);
        $this->assertCount(1, $countries);
        $this->assertArrayHasKey('id', $countries[0]);
        $this->assertArrayHasKey('translations', $countries[0]);
        $this->assertTrue(Cache::has('countries.with.translations'));
    }

    public function test_get_countries_by_language()
    {
        Cache::flush();
        $language = Language::factory()->create();
        $country = Country::factory()->create();
        $translation = CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'language_id' => $language->id,
            'name' => 'Test Country',
        ]);

        $countries = Country::getCountriesByLanguage($language->id);

        $this->assertIsArray($countries);
        $this->assertCount(1, $countries);
        $this->assertArrayHasKey('id', $countries[0]);
        $this->assertArrayHasKey('name', $countries[0]);
    }
}

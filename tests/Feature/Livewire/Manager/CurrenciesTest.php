<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\Currencies;
use Tests\Feature\LivewireTestCase;

class CurrenciesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_currencies_page_can_be_rendered()
    {
        $component = $this->livewire(Currencies::class);
        $component->assertSuccessful();
    }

    public function test_currencies_list_displays_currencies()
    {
        $currency = $this->createCurrency();

        $component = $this->livewire(Currencies::class);

        $currencies = $component->get('currencies');
        $currencyIds = $currencies->pluck('id')->toArray();
        $this->assertContains($currency->id, $currencyIds);
    }

    public function test_can_search_currencies_by_code()
    {
        $currency1 = $this->createCurrency(['code' => 'USD']);
        $currency2 = $this->createCurrency(['code' => 'EUR']);

        $component = $this->livewire(Currencies::class)
            ->set('search', 'USD');

        $currencies = $component->get('currencies');
        $currencyIds = $currencies->pluck('id')->toArray();
        $this->assertContains($currency1->id, $currencyIds);
        $this->assertNotContains($currency2->id, $currencyIds);
    }

    public function test_can_search_currencies_by_name()
    {
        $currency1 = $this->createCurrency(['name' => 'US Dollar']);
        $currency2 = $this->createCurrency(['name' => 'Euro']);

        $component = $this->livewire(Currencies::class)
            ->set('search', 'Dollar');

        $currencies = $component->get('currencies');
        $currencyIds = $currencies->pluck('id')->toArray();
        $this->assertContains($currency1->id, $currencyIds);
        $this->assertNotContains($currency2->id, $currencyIds);
    }

    public function test_can_filter_currencies_by_default()
    {
        $defaultCurrency = $this->createCurrency(['default' => true]);
        $nonDefaultCurrency = $this->createCurrency(['default' => false]);

        $component = $this->livewire(Currencies::class)
            ->set('filterDefault', '1');

        $currencies = $component->get('currencies');
        $currencyIds = $currencies->pluck('id')->toArray();
        $this->assertContains($defaultCurrency->id, $currencyIds);
        $this->assertNotContains($nonDefaultCurrency->id, $currencyIds);
    }

    public function test_can_delete_currency()
    {
        $currency = $this->createCurrency();

        $component = $this->livewire(Currencies::class)
            ->call('deleteCurrency', $currency->id);

        $this->assertDatabaseMissing('currencies', ['id' => $currency->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Currencies::class)
            ->set('search', 'test')
            ->set('filterDefault', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterDefault', '');
    }
}

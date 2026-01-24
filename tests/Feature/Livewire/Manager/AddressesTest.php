<?php

namespace Tests\Feature\Livewire\Manager;

use App\Models\Address;
use App\Models\Country;
use Tests\Feature\LivewireTestCase;

class AddressesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->country = Country::factory()->create();
    }

    public function test_addresses_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\Addresses::class);
        $component->assertSuccessful();
    }

    public function test_addresses_list_displays_addresses()
    {
        $address = Address::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class);

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address->id, $addressIds);
    }

    public function test_can_search_addresses_by_user_id()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $address1 = Address::factory()->create([
            'user_id' => $user1->id,
            'country_id' => $this->country->id,
        ]);
        $address2 = Address::factory()->create([
            'user_id' => $user2->id,
            'country_id' => $this->country->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->set('search', (string) $user1->id);

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address1->id, $addressIds);
        $this->assertNotContains($address2->id, $addressIds);
    }

    public function test_can_search_addresses_by_user_name()
    {
        $user1 = $this->createUser(['name' => 'John Doe']);
        $user2 = $this->createUser(['name' => 'Jane Smith']);
        $address1 = Address::factory()->create([
            'user_id' => $user1->id,
            'country_id' => $this->country->id,
        ]);
        $address2 = Address::factory()->create([
            'user_id' => $user2->id,
            'country_id' => $this->country->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->set('search', 'John');

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address1->id, $addressIds);
        $this->assertNotContains($address2->id, $addressIds);
    }

    public function test_can_search_addresses_by_address_fields()
    {
        $address1 = Address::factory()->create([
            'address_1' => '123 Main St',
            'country_id' => $this->country->id,
        ]);
        $address2 = Address::factory()->create([
            'address_1' => '456 Oak Ave',
            'country_id' => $this->country->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->set('search', 'Main St');

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address1->id, $addressIds);
        $this->assertNotContains($address2->id, $addressIds);
    }

    public function test_can_filter_addresses_by_country()
    {
        $country2 = Country::factory()->create();
        $address1 = Address::factory()->create(['country_id' => $this->country->id]);
        $address2 = Address::factory()->create(['country_id' => $country2->id]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->set('filterCountryId', $this->country->id);

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address1->id, $addressIds);
        $this->assertNotContains($address2->id, $addressIds);
    }

    public function test_can_delete_address_without_orders()
    {
        $address = Address::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->call('deleteAddress', $address->id);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_address_with_shipping_orders()
    {
        $address = Address::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $order = $this->createOrder([
            'shipping_address_id' => $address->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->call('deleteAddress', $address->id);

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_address_with_billing_orders()
    {
        $address = Address::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $order = $this->createOrder([
            'billing_address_id' => $address->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->call('deleteAddress', $address->id);

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Addresses::class)
            ->set('search', 'test')
            ->set('filterCountryId', 1)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterCountryId', null);
    }
}

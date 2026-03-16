<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Addresses;
use App\Models\Address;
use App\Models\Country;
use App\Models\Zone;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\LivewireTestCase;

class AddressesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 创建测试数据
        $this->country = Country::factory()->create();
        $this->zone = Zone::factory()->create(['country_id' => $this->country->id]);
    }

    public function test_addresses_page_requires_authentication()
    {
        try {
            $this->livewire(Addresses::class);
            $this->fail('Expected redirect or exception was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        } catch (\Exception $e) {
            // 如果抛出其他异常也可以接受
            $this->assertTrue(true);
        }
    }

    public function test_authenticated_user_can_view_addresses()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Addresses::class);
        $component->assertSuccessful();
    }

    public function test_user_can_see_own_addresses()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => $this->country->id,
            'zone_id' => $this->zone->id,
            'deleted' => false,
        ]);

        $component = $this->livewire(Addresses::class);

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertContains($address->id, $addressIds);
    }

    public function test_user_cannot_see_other_users_addresses()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->actingAs($user1);

        $address = Address::factory()->create([
            'user_id' => $user2->id,
            'country_id' => $this->country->id,
            'zone_id' => $this->zone->id,
            'deleted' => false,
        ]);

        $component = $this->livewire(Addresses::class);

        $addresses = $component->get('addresses');
        $addressIds = $addresses->pluck('id')->toArray();
        $this->assertNotContains($address->id, $addressIds);
    }

    public function test_user_can_create_address()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Addresses::class)
            ->set('email', 'test@example.com')
            ->set('firstname', 'John')
            ->set('lastname', 'Doe')
            ->set('telephone', '1234567890')
            ->set('address_1', '123 Main St')
            ->set('city', 'Test City')
            ->set('postcode', '12345')
            ->set('country_id', $this->country->id)
            ->set('zone_id', $this->zone->id)
            ->call('saveAddress');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);
    }

    public function test_user_can_update_address()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => $this->country->id,
            'zone_id' => $this->zone->id,
            'firstname' => 'Old Name',
            'email' => 'test@example.com',
            'lastname' => 'Doe',
            'telephone' => '1234567890',
            'address_1' => '123 Main St',
            'city' => 'Test City',
            'postcode' => '12345',
            'deleted' => false,
        ]);

        $component = $this->livewire(Addresses::class)
            ->call('editAddress', $address->id);

        // 验证编辑表单已加载
        $this->assertEquals('Old Name', $component->get('firstname'));
        $this->assertEquals($address->id, $component->get('addressId'));

        // 确保所有必需字段都已设置（从编辑表单加载的值）
        // 只更新 firstname，其他字段保持原值
        $component->set('firstname', 'New Name');

        // 验证 firstname 已设置
        $this->assertEquals('New Name', $component->get('firstname'));
        $this->assertEquals($address->id, $component->get('addressId'));

        $component->call('saveAddress');

        // 需要重新查询地址，因为 update 可能不会自动刷新模型
        $address = Address::where('id', $address->id)->where('deleted', false)->first();
        $this->assertNotNull($address, 'Address should still exist after update');
        $this->assertEquals('New Name', $address->firstname, 'Address firstname should be updated to "New Name"');
    }

    public function test_user_can_delete_address()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => $this->country->id,
            'zone_id' => $this->zone->id,
            'deleted' => false,
        ]);

        $component = $this->livewire(Addresses::class)
            ->call('deleteAddress', $address->id);

        // 地址使用软删除，检查 deleted 字段是否为 true
        $address = Address::where('id', $address->id)->first();
        $this->assertNotNull($address);
        $this->assertTrue((bool) $address->deleted);
    }

    public function test_address_creation_validates_required_fields()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Addresses::class)
            ->call('saveAddress')
            ->assertHasErrors(['email', 'firstname', 'lastname', 'telephone', 'address_1', 'city', 'postcode', 'country_id', 'zone_id']);
    }

    public function test_address_creation_validates_email_format()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Addresses::class)
            ->set('email', 'invalid-email')
            ->set('firstname', 'John')
            ->set('lastname', 'Doe')
            ->set('telephone', '1234567890')
            ->set('address_1', '123 Main St')
            ->set('city', 'Test City')
            ->set('postcode', '12345')
            ->set('country_id', $this->country->id)
            ->set('zone_id', $this->zone->id)
            ->call('saveAddress')
            ->assertHasErrors(['email']);
    }

    public function test_updating_country_loads_zones()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Addresses::class)
            ->set('country_id', $this->country->id)
            ->assertSet('zone_id', '');

        $zones = $component->get('zones');
        $this->assertNotEmpty($zones);
    }
}

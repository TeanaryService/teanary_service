<?php

namespace Tests\Feature\Livewire\Manager;

use App\Models\UserGroup;
use Tests\Feature\LivewireTestCase;

class UsersTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_users_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\Users::class);
        $component->assertSuccessful();
    }

    public function test_users_list_displays_users()
    {
        $user = $this->createUser();

        $component = $this->livewire(\App\Livewire\Manager\Users::class);

        $users = $component->get('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($user->id, $userIds);
    }

    public function test_can_search_users_by_name()
    {
        $user1 = $this->createUser(['name' => 'John Doe']);
        $user2 = $this->createUser(['name' => 'Jane Smith']);

        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->set('search', 'John');

        $users = $component->get('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertNotContains($user2->id, $userIds);
    }

    public function test_can_search_users_by_email()
    {
        $user1 = $this->createUser(['email' => 'john@example.com']);
        $user2 = $this->createUser(['email' => 'jane@example.com']);

        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->set('search', 'john@example.com');

        $users = $component->get('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertNotContains($user2->id, $userIds);
    }

    public function test_can_filter_users_by_user_group()
    {
        $userGroup = UserGroup::factory()->create();
        $user1 = $this->createUser(['user_group_id' => $userGroup->id]);
        $user2 = $this->createUser();

        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->set('filterUserGroupId', $userGroup->id);

        $users = $component->get('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertNotContains($user2->id, $userIds);
    }

    public function test_can_filter_users_by_email_verified()
    {
        $verifiedUser = $this->createUser(['email_verified_at' => now()]);
        $unverifiedUser = $this->createUser(['email_verified_at' => null]);

        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->set('filterEmailVerified', '1');

        $users = $component->get('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($verifiedUser->id, $userIds);
        $this->assertNotContains($unverifiedUser->id, $userIds);
    }

    public function test_can_delete_user()
    {
        $user = $this->createUser();

        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->call('deleteUser', $user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Users::class)
            ->set('search', 'test')
            ->set('filterUserGroupId', 1)
            ->set('filterEmailVerified', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterUserGroupId', null)
            ->assertSet('filterEmailVerified', '');
    }
}

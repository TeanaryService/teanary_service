<?php

namespace Tests\Feature\Livewire\Manager;

use Tests\Feature\LivewireTestCase;

class ManagersTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_managers_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\Managers::class);
        $component->assertSuccessful();
    }

    public function test_managers_list_displays_managers()
    {
        $manager = $this->createManager();

        $component = $this->livewire(\App\Livewire\Manager\Managers::class);

        $managers = $component->get('managers');
        $managerIds = $managers->pluck('id')->toArray();
        $this->assertContains($manager->id, $managerIds);
    }

    public function test_can_search_managers_by_name()
    {
        $manager1 = $this->createManager(['name' => 'John Manager']);
        $manager2 = $this->createManager(['name' => 'Jane Manager']);

        $component = $this->livewire(\App\Livewire\Manager\Managers::class)
            ->set('search', 'John');

        $managers = $component->get('managers');
        $managerIds = $managers->pluck('id')->toArray();
        $this->assertContains($manager1->id, $managerIds);
        $this->assertNotContains($manager2->id, $managerIds);
    }

    public function test_can_search_managers_by_email()
    {
        $manager1 = $this->createManager(['email' => 'john@example.com']);
        $manager2 = $this->createManager(['email' => 'jane@example.com']);

        $component = $this->livewire(\App\Livewire\Manager\Managers::class)
            ->set('search', 'john@example.com');

        $managers = $component->get('managers');
        $managerIds = $managers->pluck('id')->toArray();
        $this->assertContains($manager1->id, $managerIds);
        $this->assertNotContains($manager2->id, $managerIds);
    }

    public function test_can_delete_manager()
    {
        $manager = $this->createManager();

        $component = $this->livewire(\App\Livewire\Manager\Managers::class)
            ->call('deleteManager', $manager->id);

        $this->assertDatabaseMissing('managers', ['id' => $manager->id]);
    }

    public function test_can_generate_token()
    {
        $manager = $this->createManager(['token' => null]);

        $component = $this->livewire(\App\Livewire\Manager\Managers::class)
            ->call('generateToken', $manager->id);

        $manager->refresh();
        $this->assertNotNull($manager->token);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Managers::class)
            ->set('search', 'test')
            ->call('resetFilters')
            ->assertSet('search', '');
    }
}

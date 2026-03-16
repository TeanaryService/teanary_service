<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\Contacts;
use App\Models\Contact;
use Tests\Feature\LivewireTestCase;

class ContactsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_contacts_page_can_be_rendered()
    {
        $component = $this->livewire(Contacts::class);
        $component->assertSuccessful();
    }

    public function test_contacts_list_displays_contacts()
    {
        $contact = Contact::factory()->create();

        $component = $this->livewire(Contacts::class);

        $contacts = $component->get('contacts');
        $contactIds = $contacts->pluck('id')->toArray();
        $this->assertContains($contact->id, $contactIds);
    }

    public function test_can_search_contacts_by_name()
    {
        $contact1 = Contact::factory()->create(['name' => 'John Doe']);
        $contact2 = Contact::factory()->create(['name' => 'Jane Smith']);

        $component = $this->livewire(Contacts::class)
            ->set('search', 'John');

        $contacts = $component->get('contacts');
        $contactIds = $contacts->pluck('id')->toArray();
        $this->assertContains($contact1->id, $contactIds);
        $this->assertNotContains($contact2->id, $contactIds);
    }

    public function test_can_search_contacts_by_email()
    {
        $contact1 = Contact::factory()->create(['email' => 'john@example.com']);
        $contact2 = Contact::factory()->create(['email' => 'jane@example.com']);

        $component = $this->livewire(Contacts::class)
            ->set('search', 'john@example.com');

        $contacts = $component->get('contacts');
        $contactIds = $contacts->pluck('id')->toArray();
        $this->assertContains($contact1->id, $contactIds);
        $this->assertNotContains($contact2->id, $contactIds);
    }

    public function test_can_search_contacts_by_message()
    {
        $contact1 = Contact::factory()->create(['message' => '测试消息1']);
        $contact2 = Contact::factory()->create(['message' => '其他消息']);

        $component = $this->livewire(Contacts::class)
            ->set('search', '测试');

        $contacts = $component->get('contacts');
        $contactIds = $contacts->pluck('id')->toArray();
        $this->assertContains($contact1->id, $contactIds);
        $this->assertNotContains($contact2->id, $contactIds);
    }

    public function test_can_filter_contacts_by_date_range()
    {
        $contact1 = Contact::factory()->create(['created_at' => now()->subDays(5)]);
        $contact2 = Contact::factory()->create(['created_at' => now()->subDays(2)]);

        $component = $this->livewire(Contacts::class)
            ->set('createdFrom', now()->subDays(3)->format('Y-m-d'))
            ->set('createdUntil', now()->format('Y-m-d'));

        $contacts = $component->get('contacts');
        $contactIds = $contacts->pluck('id')->toArray();
        $this->assertContains($contact2->id, $contactIds);
        $this->assertNotContains($contact1->id, $contactIds);
    }

    public function test_can_delete_contact()
    {
        $contact = Contact::factory()->create();

        $component = $this->livewire(Contacts::class)
            ->call('deleteContact', $contact->id);

        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Contacts::class)
            ->set('search', 'test')
            ->set('createdFrom', '2024-01-01')
            ->set('createdUntil', '2024-12-31')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('createdFrom', null)
            ->assertSet('createdUntil', null);
    }
}

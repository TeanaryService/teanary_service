<?php

namespace Tests\Feature\Livewire;

use Tests\Feature\LivewireTestCase;

class IndexPageTest extends LivewireTestCase
{
    public function test_index_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\IndexPage::class);
        $component->assertSuccessful();
    }
}

<?php

namespace Tests\Feature\Livewire;

use App\Livewire\IndexPage;
use Tests\Feature\LivewireTestCase;

class IndexPageTest extends LivewireTestCase
{
    public function test_index_page_can_be_rendered()
    {
        $component = $this->livewire(IndexPage::class);
        $component->assertSuccessful();
    }
}

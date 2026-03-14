<?php

namespace App\Livewire;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Category;
use Livewire\Component;

class Home extends Component
{
    use UsesLocaleCurrency;

    public $categories = [];

    public function mount()
    {
        $langId = $this->getCurrentLanguage()?->id;
        $this->categories = Category::getCategoriesForLanguage($langId);
    }

    public function render()
    {
        $categories = collect($this->categories)->filter(fn ($c) => is_array($c) && isset($c['slug'], $c['name'], $c['image_url']))->values();

        return view('livewire.home', [
            'categories' => $categories,
        ]);
    }
}

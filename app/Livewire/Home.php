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
        return view('livewire.home', [
            'categories' => $this->categories,
        ]);
    }
}

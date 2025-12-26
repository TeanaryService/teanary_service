<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class Home extends Component
{
    public $categories = [];

    public function mount()
    {
        $langId = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $this->categories = Category::getCategoriesForLanguage($langId);
    }

    public function render()
    {
        return view('livewire.home', [
            'categories' => $this->categories,
        ]);
    }
}

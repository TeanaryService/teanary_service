<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;

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

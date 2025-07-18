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
        // $locale = app()->getLocale();
        // $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        $this->categories = Category::getCachedCategories();
    }

    public function render()
    {
        return view('livewire.home', [
            'categories' => $this->categories,
        ]);
    }
}

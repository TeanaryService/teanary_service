<?php

namespace App\View\Components;

use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Footer extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $langId = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $categories = Category::getCategoriesForLanguage($langId);

        return view('components.footer', [
            'categories' => $categories,
        ]);
    }
}

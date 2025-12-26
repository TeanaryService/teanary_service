<?php

namespace App\View\Components;

use App\Services\LocaleCurrencyService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LanguageSwitch extends Component
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
        $service = new LocaleCurrencyService;
        $languages = $service->getLanguages();

        $languageCode = app()->getLocale();
        $selectedLanguage = $service->getLanguageByCode($languageCode);

        return view('components.language-switch', [
            'languages' => $languages,
            'selectedLanguage' => $selectedLanguage,
        ]);
    }
}

<?php

namespace App\Filament\Manager\Widgets;

use App\Services\LocaleCurrencyService;
use Livewire\Component;

class LanguageCurrencySwitcher extends Component
{
    public $selectedLanguage;

    public $selectedCurrency;

    public $languages;

    public $currencies;

    public function mount()
    {
        $service = new LocaleCurrencyService;
        $this->languages = $service->getLanguages();
        $this->currencies = $service->getCurrencies();

        $languageCode = app()->getLocale();
        $this->selectedLanguage = $service->getLanguageByCode($languageCode);

        $currencyCode = session('currency');
        $this->selectedCurrency = $service->getCurrencyByCode($currencyCode);
    }

    public function render()
    {
        return view('filament.widgets.language-currency-switcher');
    }
}

<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Services\LocaleCurrencyService;

class LocaleCurrencySwitcher extends Component
{
    public $selectedLanguage;
    public $selectedCurrency;

    public $languages;
    public $currencies;

    public function mount()
    {
        $service = new LocaleCurrencyService();
        $this->languages = $service->getLanguages();
        $this->currencies = $service->getCurrencies();

        $languageCode = session('lang');
        $this->selectedLanguage = $service->getLanguageByCode($languageCode);

        $currencyCode = session('currency');
        $this->selectedCurrency = $service->getCurrencyByCode($currencyCode);
    }


    public function render()
    {
        return view('livewire.components.locale-currency-switcher');
    }
}
<?php

namespace App\Observers;

use App\Models\Language;
use App\Services\LocaleCurrencyService;

class LanguageObserver
{
    public function saved(Language $language)
    {
        (new LocaleCurrencyService())->clearLanguagesCache();
    }

    public function deleted(Language $language)
    {
        (new LocaleCurrencyService())->clearLanguagesCache();
    }
}

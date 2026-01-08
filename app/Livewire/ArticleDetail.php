<?php

namespace App\Livewire;

use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class ArticleDetail extends Component
{
    public ?Article $article = null;

    public function mount($slug)
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());

        $this->article = Article::with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $lang?->id)])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.article-detail');
    }
}

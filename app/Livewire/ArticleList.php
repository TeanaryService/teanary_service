<?php

namespace App\Livewire;

use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());

        $articles = Article::query()
            ->with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $lang?->id)])
            ->where('is_published', true)
            ->latest()
            ->paginate(10);

        return view('livewire.article-list', compact('articles'));
    }
}

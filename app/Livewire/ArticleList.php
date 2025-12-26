<?php

namespace App\Livewire;

use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class ArticleList extends Component
{
    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());
        $search = request('search');

        $query = Article::query()
            ->with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $lang?->id)])
            ->where('is_published', true);

        if ($search) {
            $ids = Article::search($search)->keys();
            $query->whereIn('id', $ids);
        }

        $articles = $query->latest()->paginate(10);

        return view('livewire.article-list', [
            'articles' => $articles,
            'search' => $search,
        ]);
    }
}

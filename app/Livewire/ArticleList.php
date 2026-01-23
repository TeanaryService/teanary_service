<?php

namespace App\Livewire;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Article;
use Livewire\Component;

class ArticleList extends Component
{
    use UsesLocaleCurrency;

    public function render()
    {
        $lang = $this->getCurrentLanguage();
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

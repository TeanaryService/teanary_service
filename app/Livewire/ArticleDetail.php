<?php

namespace App\Livewire;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Article;
use Livewire\Component;

class ArticleDetail extends Component
{
    use UsesLocaleCurrency;

    public ?Article $article = null;

    public function mount($slug)
    {
        $lang = $this->getCurrentLanguage();

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

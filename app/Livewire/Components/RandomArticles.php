<?php

namespace App\Livewire\Components;

use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class RandomArticles extends Component
{
    public $limit;

    public $class = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';  // 默认网格布局

    public function mount($limit = 3, $class = null)
    {
        $this->limit = $limit;

        if ($class) {
            $this->class = $class;
        }
    }

    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());

        $articles = Article::query()
            ->with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $lang?->id)])
            ->where('is_published', true)
            ->inRandomOrder()
            ->limit($this->limit)
            ->get();

        return view('livewire.components.random-articles', compact('articles'));
    }
}

<?php

namespace App\Livewire;

use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ArticleList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public function mount()
    {
        $this->search = request('search', '');
    }

    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());

        $query = Article::query()
            ->with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $lang?->id)])
            ->where('is_published', true);

        if ($this->search) {
            $searchResults = Article::search($this->search)->get();
            if ($searchResults->isNotEmpty()) {
                $query->whereIn('id', $searchResults->pluck('id'));
            } else {
                $query->whereRaw('1 = 0'); // 没有搜索结果
            }
        }

        $articles = $query->latest()->paginate(10);

        return view('livewire.article-list', compact('articles'));
    }

    public function updating($name, $value)
    {
        if ($name === 'search') {
            $this->resetPage();
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }
}

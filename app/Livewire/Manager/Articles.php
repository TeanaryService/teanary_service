<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Article;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class Articles extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterIsPublished = '';
    public array $filterTranslationStatus = [];
    public ?int $filterUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterIsPublished(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTranslationStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterIsPublished = '';
        $this->filterTranslationStatus = [];
        $this->filterUserId = null;
        $this->resetPage();
    }

    public function deleteArticle(int $id): void
    {
        $article = Article::findOrFail($id);
        $article->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function togglePublish(int $id): void
    {
        $article = Article::findOrFail($id);
        $article->is_published = !$article->is_published;
        $article->save();
        session()->flash('message', $article->is_published ? __('manager.article.published') : __('manager.article.unpublished'));
    }

    public function getArticlesProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = Article::query()
            ->with(['user', 'articleTranslations']);

        // 搜索：通过翻译标题和摘要搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('articleTranslations', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('summary', 'like', '%' . $search . '%');
            });
        }

        // 筛选：发布状态
        if ($this->filterIsPublished !== '') {
            $query->where('is_published', $this->filterIsPublished === '1');
        }

        // 筛选：翻译状态
        if (!empty($this->filterTranslationStatus)) {
            $query->whereIn('translation_status', $this->filterTranslationStatus);
        }

        // 筛选：用户
        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getArticleTitle($article, $lang)
    {
        $translation = $article->articleTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->title) {
            return $translation->title;
        }
        $first = $article->articleTranslations->first();
        return $first ? $first->title : $article->slug;
    }

    public function getArticleSummary($article, $lang)
    {
        $translation = $article->articleTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->summary) {
            return $translation->summary;
        }
        $first = $article->articleTranslations->first();
        return $first ? ($first->summary ?? '') : '';
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $users = \App\Models\User::orderBy('name')->get();

        return view('livewire.manager.articles', [
            'articles' => $this->articles,
            'lang' => $lang,
            'users' => $users,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Article;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Articles extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasNavigationRedirect;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public string $filterIsPublished = '';
    public ?string $filterTranslationStatus = null;

    public function updatingFilterIsPublished(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTranslationStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterIsPublished = '';
        $this->filterTranslationStatus = null;
        $this->resetPage();
    }

    public function deleteArticle(int $id): void
    {
        $this->deleteModel(Article::class, $id);
    }

    public function togglePublish(int $id): void
    {
        $article = Article::findOrFail($id);
        $article->is_published = ! $article->is_published;
        $article->save();
        $this->dispatch('flash-message', type: 'success', message: $article->is_published ? __('manager.article.published') : __('manager.article.unpublished'));
    }

    protected function getCurrentPageItems()
    {
        return $this->articles->getCollection();
    }

    public function batchDeleteArticles(): void
    {
        $this->batchDelete(Article::class);
    }

    public function batchSetArticleTranslationStatus(string $status): void
    {
        $this->batchUpdateTranslationStatus(Article::class, $status);
    }

    public function batchSetPublishedStatus(bool $isPublished): void
    {
        $this->batchUpdatePublishedStatus(Article::class, $isPublished);
    }

    #[Computed]
    public function articles()
    {
        $lang = $this->getCurrentLanguage();

        $query = Article::query()
            ->with(['user', 'articleTranslations']);

        // 搜索：通过翻译标题和摘要搜索
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('articleTranslations', function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('summary', 'like', '%'.$search.'%');
            });
        }

        // 筛选：发布状态
        if ($this->filterIsPublished !== '') {
            $query->where('is_published', $this->filterIsPublished === '1');
        }

        // 筛选：翻译状态
        if ($this->filterTranslationStatus) {
            $query->where('translation_status', $this->filterTranslationStatus);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getArticleTitle($article, $lang)
    {
        return $this->translatedField($article->articleTranslations, $lang, 'title', $article->slug);
    }

    public function getArticleSummary($article, $lang)
    {
        return $this->translatedField($article->articleTranslations, $lang, 'summary', '');
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();

        return view('livewire.manager.articles', [
            'articles' => $this->articles,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

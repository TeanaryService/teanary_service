<?php

namespace App\Observers;

use App\Models\Article;
use App\Traits\HandlesEditorUploads;

class ArticleObserver
{
    use HandlesEditorUploads;

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "deleting" event.
     */
    public function deleting(Article $article): void
    {
        // 删除正文文件
        $article->articleTranslations()->each(function ($translation) {
            $this->deleteEditorUploadsFromHtml($translation->content);
        });
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        //
    }
}

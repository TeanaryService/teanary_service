<?php

namespace App\Observers;

use App\Models\ArticleTranslation;
use App\Traits\HandlesEditorUploads;

class ArticleTranslationObserver
{
    use HandlesEditorUploads;

    /**
     * Handle the ArticleTranslation "created" event.
     */
    public function created(ArticleTranslation $articleTranslation): void
    {
        //
        $this->handleEditorUploadsFromHtml($articleTranslation->content);
    }

    public function updating(ArticleTranslation $articleTranslation): void
    {
        //
        $oldContent = $articleTranslation->getOriginal('content');
        $newContent = $articleTranslation->content;

        $this->syncEditorUploadsFromHtml($oldContent, $newContent);
    }

    /**
     * Handle the ArticleTranslation "updated" event.
     */
    public function updated(ArticleTranslation $articleTranslation): void
    {
        //
    }

    public function deleting(ArticleTranslation $articleTranslation)
    {
        $this->deleteEditorUploadsFromHtml($articleTranslation->content);
    }

    /**
     * Handle the ArticleTranslation "deleted" event.
     */
    public function deleted(ArticleTranslation $articleTranslation): void
    {
        //
    }
}

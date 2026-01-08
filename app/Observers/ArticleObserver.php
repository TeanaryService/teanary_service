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
     * 
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Article $article): void
    {
        // 删除文章翻译
        $article->articleTranslations()->each(function ($translation) {
            // 删除正文文件
            $this->deleteEditorUploadsFromHtml($translation->content);
            $translation->delete();
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

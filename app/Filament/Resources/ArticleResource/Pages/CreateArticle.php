<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function handleRecordCreation(array $data): Article
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $article = static::getModel()::create($data);

        // 处理多语言
        foreach ($translations as $languageId => $fields) {
            $article->articleTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                [
                    'title' => $fields['title'] ?? '',
                    'summary' => $fields['summary'] ?? '',
                    'content' => $fields['content'] ?? '',
                ]
            );
        }

        return $article;
    }
}

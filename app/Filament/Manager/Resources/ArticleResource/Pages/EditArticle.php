<?php

namespace App\Filament\Manager\Resources\ArticleResource\Pages;

use App\Filament\Manager\Resources\ArticleResource;
use App\Models\Article;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function handleRecordUpdate($record, array $data): Article
    {
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        $record->update($data);

        // 处理多语言
        foreach ($translations as $languageId => $fields) {
            $record->articleTranslations()->updateOrCreate(
                ['language_id' => $languageId],
                [
                    'title' => $fields['title'] ?? '',
                    'summary' => $fields['summary'] ?? '',
                    'content' => $fields['content'] ?? '',
                ]
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 回填多语言
        $translations = [];
        if (isset($this->record->articleTranslations)) {
            foreach ($this->record->articleTranslations as $translation) {
                $translations[$translation->language_id] = [
                    'title' => $translation->title,
                    'summary' => $translation->summary,
                    'content' => $translation->content,
                ];
            }
        }
        $data['translations'] = $translations;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

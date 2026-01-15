<?php

namespace App\Filament\Manager\Resources\AttributeResource\RelationManagers;

use App\Filament\Manager\Resources\AttributeValueResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttributeValuesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.attribute.attribute_values');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.attribute.attribute_values');
    }

    protected static string $relationship = 'attributeValues';

    public function form(Form $form): Form
    {
        return AttributeValueResource::form($form);
    }

    public function table(Table $table): Table
    {
        return AttributeValueResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.attribute.attribute_values')),
            ]);
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(
                static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)
            )
            ->form(fn (Form $form): Form => $this->form($form->columns(1)))
            ->mutateRecordDataUsing(function (array $data, Model $record): array {
                $translations = [];

                foreach ($record->attributeValueTranslations as $translation) {
                    $translations[$translation->language_id] = [
                        'name' => $translation->name,
                    ];
                }

                $data['translations'] = $translations;

                return $data;
            })
            ->after(function (Model $record, array $data): void {
                // 保存 translations
                foreach ($data['translations'] ?? [] as $languageId => $fields) {
                    $record->attributeValueTranslations()->updateOrCreate(
                        ['language_id' => $languageId],
                        ['name' => $fields['name'] ?? '']
                    );
                }
            });
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->form(fn (Form $form): Form => $this->form($form->columns(1)))
            ->after(function (Model $record, array $data): void {
                foreach ($data['translations'] ?? [] as $languageId => $fields) {
                    $record->attributeValueTranslations()->create([
                        'language_id' => $languageId,
                        'name' => $fields['name'] ?? '',
                    ]);
                }
            });
    }
}

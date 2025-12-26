<?php

namespace App\Filament\Resources\SpecificationResource\RelationManagers;

use App\Filament\Resources\SpecificationValueResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SpecificationValuesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.specification.specification_values');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.specification.specification_values');
    }

    protected static string $relationship = 'specificationValues';

    public function form(Form $form): Form
    {
        return SpecificationValueResource::form($form);
    }

    public function table(Table $table): Table
    {
        return SpecificationValueResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.specification.specification_values')),
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

                foreach ($record->specificationValueTranslations as $translation) {
                    $translations[$translation->language_id] = [
                        'name' => $translation->name,
                    ];
                }

                $data['translations'] = $translations;

                return $data;
            })
            ->after(function (Model $record, array $data): void {
                foreach ($data['translations'] ?? [] as $languageId => $fields) {
                    $record->specificationValueTranslations()->updateOrCreate(
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
                    $record->specificationValueTranslations()->create([
                        'language_id' => $languageId,
                        'name' => $fields['name'] ?? '',
                    ]);
                }
            });
    }
}

<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Filament\Resources\ZoneResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZonesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.country.zones');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.country.zones');
    }

    protected static string $relationship = 'zones';

    public function form(Form $form): Form
    {
        return ZoneResource::form($form);
    }

    public function table(Table $table): Table
    {
        return ZoneResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.country.zones')),
            ]);
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(
                static fn(RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)
            )
            ->form(fn(Form $form): Form => $this->form($form->columns(1)))
            ->mutateRecordDataUsing(function (array $data, Model $record): array {
                $translations = [];

                foreach ($record->zoneTranslations as $translation) {
                    $translations[$translation->language_id] = [
                        'name' => $translation->name,
                    ];
                }

                $data['translations'] = $translations;

                return $data;
            })
            ->after(function (Model $record, array $data): void {
                foreach ($data['translations'] ?? [] as $languageId => $fields) {
                    $record->zoneTranslations()->updateOrCreate(
                        ['language_id' => $languageId],
                        ['name' => $fields['name'] ?? '']
                    );
                }
            });
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->form(fn(Form $form): Form => $this->form($form->columns(1)))
            ->after(function (Model $record, array $data): void {
                foreach ($data['translations'] ?? [] as $languageId => $fields) {
                    $record->zoneTranslations()->create([
                        'language_id' => $languageId,
                        'name' => $fields['name'] ?? '',
                    ]);
                }
            });
    }
}

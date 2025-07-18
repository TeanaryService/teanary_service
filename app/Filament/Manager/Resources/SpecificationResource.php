<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\SpecificationResource\Pages;
use App\Filament\Manager\Resources\SpecificationResource\RelationManagers;
use App\Filament\Manager\Resources\SpecificationResource\RelationManagers\SpecificationValuesRelationManager;
use App\Models\Specification;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecificationResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Specification::class;
    protected static ?int $navigationSort = 205;

    public static function getLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.SpecificationResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.SpecificationResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.SpecificationResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->specificationTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament_specification.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament_specification.name')),
                // ...existing code...
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 多语言 name 列
                Tables\Columns\TextColumn::make('specificationTranslations.name')
                    ->label(__('filament_specification.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->specificationTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationTranslations->first();
                        return $first ? $first->name : '';
                    }),
                // ...existing code...
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions()
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
            SpecificationValuesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecifications::route('/'),
            'create' => Pages\CreateSpecification::route('/create'),
            'edit' => Pages\EditSpecification::route('/{record}/edit'),
        ];
    }
}

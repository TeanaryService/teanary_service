<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\LanguageResource\Pages;
use App\Models\Language;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Language::class;

    protected static ?int $navigationSort = 401;

    public static function getLabel(): string
    {
        return __('filament.LanguageResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.LanguageResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.LanguageResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.LanguageResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.LanguageResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament.language.code'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.language.name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament.language.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.language.name'))
                    ->searchable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions(),
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}

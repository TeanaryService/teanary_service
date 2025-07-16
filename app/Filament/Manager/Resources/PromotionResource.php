<?php

namespace App\Filament\Manager\Resources;

use App\Enums\PromotionTypeEnum;
use App\Filament\Manager\Resources\PromotionResource\Pages;
use App\Filament\Manager\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Promotion::class;

    public static function getLabel(): string
    {
        return __('filament.PromotionResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.PromotionResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.PromotionResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.PromotionResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.PromotionResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.PromotionResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label(__('filament_promotion.type'))
                    ->options(PromotionTypeEnum::options())
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label(__('filament_promotion.starts_at'))
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label(__('filament_promotion.ends_at'))
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_promotion.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label(__('filament_promotion.type')),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('filament_promotion.starts_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('filament_promotion.ends_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_promotion.active'))
                    ->boolean(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}

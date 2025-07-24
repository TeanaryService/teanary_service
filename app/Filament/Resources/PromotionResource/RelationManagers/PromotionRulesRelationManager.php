<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'promotionRules';

    public static function getLabel(): string
    {
        return __('filament.promotion.promotion_rules');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.promotion.promotion_rules');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('filament.promotion.image'))
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull()
                    ->required()
                    ->collection('image'),
                Forms\Components\Select::make('condition_type')
                    ->label(__('filament.promotion.condition_type'))
                    ->options(PromotionConditionTypeEnum::options())
                    ->required(),
                Forms\Components\TextInput::make('condition_value')
                    ->label(__('filament.promotion.condition_value'))
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('discount_type')
                    ->label(__('filament.promotion.discount_type'))
                    ->options(PromotionDiscountTypeEnum::options())
                    ->required(),
                Forms\Components\TextInput::make('discount_value')
                    ->label(__('filament.promotion.discount_value'))
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('filament.promotion.image'))
                    ->collection('image'),
                Tables\Columns\TextColumn::make('condition_type')
                    ->label(__('filament.promotion.condition_type'))
                    ->formatStateUsing(fn(PromotionConditionTypeEnum $state): string =>  $state->label()),
                Tables\Columns\TextColumn::make('condition_value')
                    ->label(__('filament.promotion.condition_value')),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label(__('filament.promotion.discount_type'))
                    ->formatStateUsing(fn(PromotionDiscountTypeEnum $state): string => $state->label()),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label(__('filament.promotion.discount_value')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.promotion.add_rule')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('filament.promotion.edit_rule')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('filament.promotion.delete_rule')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('filament.promotion.delete_rule')),
                ]),
            ]);
    }
}

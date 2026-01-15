<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CartResource\Pages;
use App\Filament\Manager\Resources\CartResource\RelationManagers\CartItemsRelationManager;
use App\Models\Cart;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CartResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Cart::class;

    protected static ?int $navigationSort = 103;

    public static function getLabel(): string
    {
        return __('filament.CartResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.CartResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.CartResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.CartResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.CartResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.cart.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.cart.user_id'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->helperText(__('filament.cart.user_id_helper')),
                        Forms\Components\TextInput::make('session_id')
                            ->label(__('filament.cart.session_id'))
                            ->maxLength(255)
                            ->default(null)
                            ->helperText(__('filament.cart.session_id_helper'))
                            ->visible(fn ($record) => $record !== null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'user',
                        'cartItems',
                    ])
                    ->withCount('cartItems')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament.cart.id'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.cart.user_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cart_items_count')
                    ->label(__('filament.cart.items_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('session_id')
                    ->label(__('filament.cart.session_id'))
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('filament.cart.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_items')
                    ->label(__('filament.cart.has_items'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->has('cartItems')),
                Tables\Filters\Filter::make('empty')
                    ->label(__('filament.cart.empty'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->doesntHave('cartItems')),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
    }

    public static function getRelations(): array
    {
        return [
            //
            CartItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }
}

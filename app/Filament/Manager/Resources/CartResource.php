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
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.cart.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->default(null),
                // Forms\Components\TextInput::make('session_id')
                //     ->label(__('filament.cart.session_id'))
                //     ->maxLength(255)
                //     ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.cart.user_id')),
                // Tables\Columns\TextColumn::make('session_id')
                //     ->label(__('filament.cart.session_id'))
                //     ->searchable(),
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

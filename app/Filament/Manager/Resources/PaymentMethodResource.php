<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\PaymentMethodResource\Pages;
use App\Filament\Manager\Resources\PaymentMethodResource\RelationManagers;
use App\Models\PaymentMethod;
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

class PaymentMethodResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = PaymentMethod::class;

    public static function getLabel(): string
    {
        return __('filament.PaymentMethodResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.PaymentMethodResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.PaymentMethodResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.PaymentMethodResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.PaymentMethodResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.PaymentMethodResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament_payment_method.code'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_payment_method.active'))
                    ->required(),
                Forms\Components\TextInput::make('api_url')
                    ->label(__('filament_payment_method.api_url'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament_payment_method.code'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_payment_method.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('api_url')
                    ->label(__('filament_payment_method.api_url'))
                    ->searchable(),
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}

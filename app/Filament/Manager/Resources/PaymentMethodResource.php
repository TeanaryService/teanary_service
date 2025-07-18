<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\PaymentMethodResource\Pages;
use App\Filament\Manager\Resources\PaymentMethodResource\RelationManagers;
use App\Models\PaymentMethod;
use App\Services\LocaleCurrencyService;
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
    protected static ?int $navigationSort = 403;

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
                            $translation = $model->paymentMethodTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return Forms\Components\TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament_payment_method.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament_payment_method.name')),
                Forms\Components\TextInput::make('code')
                    ->label(__('filament_payment_method.code'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('api_url')
                    ->label(__('filament_payment_method.api_url'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_payment_method.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('paymentMethodTranslations.name')
                    ->label(__('filament_payment_method.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->paymentMethodTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->paymentMethodTranslations->first();
                        return $first ? $first->name : '';
                    }),
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
            'index' => getFilamentUrl(Pages\ListPaymentMethods::class, '/'),
            'create' => getFilamentUrl(Pages\CreatePaymentMethod::class, '/create'),
            'edit' => getFilamentUrl(Pages\EditPaymentMethod::class, '/{record}/edit'),
        ];
    }
}

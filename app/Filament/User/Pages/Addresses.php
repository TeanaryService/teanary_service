<?php

namespace App\Filament\User\Pages;

use App\Models\Address;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Services\LocaleCurrencyService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class Addresses extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.user.pages.addresses';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    public static function getNavigationLabel(): string
    {
        return __('app.addresses.my_addresses');
    }

    public function getTitle(): string
    {
        return __('app.addresses.my_addresses');
    }

    public ?array $data = [];

    public ?Address $address = null;

    public bool $showForm = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(Address::query()->where('user_id', Auth::id())->where('deleted', false)->with(['country.countryTranslations', 'zone.zoneTranslations']))
            ->columns([
                TextColumn::make('firstname')
                    ->label(__('app.addresses.firstname'))
                    ->searchable(),
                TextColumn::make('lastname')
                    ->label(__('app.addresses.lastname'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('app.email'))
                    ->searchable(),
                TextColumn::make('telephone')
                    ->label(__('app.addresses.telephone'))
                    ->searchable(),
                TextColumn::make('address_1')
                    ->label(__('app.addresses.address_1'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('app.addresses.city'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('app.addresses.country'))
                    ->formatStateUsing(fn ($record) => $record->country?->countryTranslations->first()?->name ?? ''),
                TextColumn::make('zone.name')
                    ->label(__('app.addresses.zone'))
                    ->formatStateUsing(fn ($record) => $record->zone?->zoneTranslations->first()?->name ?? ''),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        $this->getAddressFormSchema(),
                    ])
                    ->fillForm(function (Address $record) {
                        return $record->toArray();
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        return $data;
                    })
                    ->using(function (Address $record, array $data): Address {
                        $record->update($data);
                        return $record;
                    })
                    ->successNotification(
                        Notification::make()
                            ->title(__('app.addresses.address_saved'))
                            ->success()
                    ),
                DeleteAction::make()
                    ->using(function (Address $record) {
                        $record->deleted = true;
                        $record->save();
                    })
                    ->successNotification(
                        Notification::make()
                            ->title(__('app.addresses.address_deleted'))
                            ->success()
                    ),
            ]);
    }

    protected function getAddressFormSchema(): array
    {
        $localeService = app(\App\Services\LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $localeService->getLanguageByCode($locale);

        return [
            TextInput::make('email')
                ->label(__('app.email'))
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('firstname')
                ->label(__('app.addresses.firstname'))
                ->required()
                ->maxLength(255),
            TextInput::make('lastname')
                ->label(__('app.addresses.lastname'))
                ->required()
                ->maxLength(255),
            TextInput::make('telephone')
                ->label(__('app.addresses.telephone'))
                ->required()
                ->maxLength(255),
            TextInput::make('address_1')
                ->label(__('app.addresses.address_1'))
                ->required()
                ->maxLength(255),
            TextInput::make('city')
                ->label(__('app.addresses.city'))
                ->required()
                ->maxLength(255),
            TextInput::make('postcode')
                ->label(__('app.addresses.postcode'))
                ->required()
                ->maxLength(20),
            Select::make('country_id')
                ->label(__('app.addresses.country'))
                ->relationship('country', 'name')
                ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                    $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                    if ($translation && $translation->name) {
                        return $translation->name;
                    }
                    $first = $record->countryTranslations->first();
                    return $first ? $first->name : $record->name;
                })
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($set) {
                    $set('zone_id', null);
                }),
            Select::make('zone_id')
                ->label(__('app.addresses.zone'))
                ->options(function ($get) use ($lang) {
                    $countryId = $get('country_id');
                    if (!$countryId) {
                        return [];
                    }
                    $zones = \App\Models\Zone::where('country_id', $countryId)->with('zoneTranslations')->get();
                    $options = [];
                    foreach ($zones as $zone) {
                        $translation = $zone->zoneTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            $options[$zone->id] = $translation->name;
                        } else {
                            $first = $zone->zoneTranslations->first();
                            $options[$zone->id] = $first ? $first->name : $zone->name;
                        }
                    }
                    return $options;
                })
                ->searchable()
                ->preload()
                ->required(fn ($get) => !empty($get('country_id')))
                ->visible(fn ($get) => !empty($get('country_id')))
                ->reactive(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(__('app.addresses.add_address'))
                ->form($this->getAddressFormSchema())
                ->action(function (array $data) {
                    $data['user_id'] = Auth::id();
                    Address::create($data);
                    Notification::make()
                        ->title(__('app.addresses.address_saved'))
                        ->success()
                        ->send();
                    $this->resetTable();
                }),
        ];
    }
}

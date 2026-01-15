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
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Addresses extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.user.pages.addresses';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    public ?array $data = [];

    public ?Address $address = null;

    public bool $showForm = false;

    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

    public static function getNavigationLabel(): string
    {
        return __('app.addresses.my_addresses');
    }

    public function getTitle(): string
    {
        return __('app.addresses.my_addresses');
    }

    public function getAddressesProperty()
    {
        return Address::query()
            ->where('user_id', Auth::id())
            ->where('deleted', false)
            ->with(['country.countryTranslations', 'zone.zoneTranslations'])
            ->latest()
            ->paginate(10);
    }

    public function editAddress(int $addressId): void
    {
        $this->address = Address::where('user_id', Auth::id())
            ->where('id', $addressId)
            ->where('deleted', false)
            ->firstOrFail();

        $this->data = $this->address->toArray();
        $this->showForm = true;
    }

    public function cancelEdit(): void
    {
        $this->reset(['address', 'data', 'showForm']);
    }

    public function saveAddress(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = Auth::id();

        if ($this->address) {
            $this->address->update($data);
            Notification::make()
                ->title(__('app.addresses.address_saved'))
                ->success()
                ->send();
        } else {
            Address::create($data);
            Notification::make()
                ->title(__('app.addresses.address_saved'))
                ->success()
                ->send();
        }

        $this->reset(['address', 'data', 'showForm']);
        $this->resetPage();
    }

    public function deleteAddress(int $addressId): void
    {
        $address = Address::where('user_id', Auth::id())
            ->where('id', $addressId)
            ->where('deleted', false)
            ->firstOrFail();

        $address->update(['deleted' => true]);

        Notification::make()
            ->title(__('app.addresses.address_deleted'))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function createAddress(): void
    {
        $this->reset(['address', 'data']);
        $this->showForm = true;
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
                ->live()
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
                ->live(),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getAddressFormSchema())
                    ->statePath('data')
            ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(__('app.addresses.add_new'))
                ->icon('heroicon-o-plus')
                ->color('success')
                ->visible(fn () => !$this->showForm)
                ->action('createAddress'),
        ];
    }
}

<?php

namespace App\Filament\User\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('app.nickname'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('app.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),
                TextInput::make('password')
                    ->label(__('app.password'))
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->confirmed()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                TextInput::make('password_confirmation')
                    ->label(__('app.password_confirmation'))
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->dehydrated(false),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['password_confirmation']);

        return $data;
    }
}

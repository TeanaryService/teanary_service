<?php

namespace App\Filament\Personal\Pages\Auth;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('filament_user.avatar'))
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->columnSpanFull()
                    ->required()
                    ->collection('avatars')
                    ->avatar(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}

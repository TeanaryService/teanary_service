<?php

namespace App\Filament\User\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.user.pages.profile';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    public static function getNavigationLabel(): string
    {
        return __('app.profile');
    }

    public function getTitle(): string
    {
        return __('app.profile');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->getMedia('avatars')->map(fn ($media) => $media->getUrl())->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('app.profile'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('app.nickname'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('auth.email'))
                            ->email()
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('password')
                            ->label(__('auth.new_password'))
                            ->password()
                            ->minLength(6)
                            ->confirmed()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText(__('app.password_leave_blank')),
                        TextInput::make('password_confirmation')
                            ->label(__('app.password_confirmation'))
                            ->password()
                            ->minLength(6)
                            ->dehydrated(false),
                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                            ->label(__('app.avatar'))
                            ->collection('avatars')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->maxSize(2048)
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        $user->name = $data['name'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // 头像上传由 SpatieMediaLibraryFileUpload 自动处理

        Notification::make()
            ->title(__('app.edit_user_success'))
            ->success()
            ->send();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(__('app.save'))
                ->submit('save'),
        ];
    }
}

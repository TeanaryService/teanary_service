<?php

namespace App\Livewire\Manager;

use App\Models\User;
use App\Models\UserGroup;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;

class UserDetail extends Component
{
    use WithFileUploads;

    public User $user;
    public $name;
    public $email;
    public $userGroupId;
    public $emailVerifiedAt;
    public $password;
    public $passwordConfirmation;
    public $avatar;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'userGroupId' => 'nullable|exists:user_groups,id',
        'emailVerifiedAt' => 'nullable|date',
        'password' => 'nullable|min:8|same:passwordConfirmation',
        'passwordConfirmation' => 'nullable|min:8',
        'avatar' => 'nullable|image|max:2048',
    ];

    public function mount($user): void
    {
        $this->user = User::with(['userGroup.userGroupTranslations', 'orders'])->findOrFail($user);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->userGroupId = $this->user->user_group_id;
        $this->emailVerifiedAt = $this->user->email_verified_at?->format('Y-m-d\TH:i');
    }

    public function updatedEmail($value): void
    {
        $this->rules['email'] = 'required|email|max:255|unique:users,email,' . $this->user->id;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'user_group_id' => $this->userGroupId,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->emailVerifiedAt) {
            $data['email_verified_at'] = $this->emailVerifiedAt;
        } else {
            $data['email_verified_at'] = null;
        }

        $this->user->update($data);

        // 处理头像上传
        if ($this->avatar) {
            $this->user->clearMediaCollection('avatars');
            $this->user->addMedia($this->avatar->getRealPath())
                ->usingName($this->avatar->getClientOriginalName())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatars');
            $this->avatar = null;
        }

        session()->flash('message', __('app.save_success'));
    }

    public function getUserGroupsProperty()
    {
        $locale = app()->getLocale();
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
        
        return UserGroup::with('userGroupTranslations')
            ->get()
            ->map(function ($group) use ($lang) {
                $translation = $group->userGroupTranslations->where('language_id', $lang?->id)->first();
                $group->display_name = $translation?->name ?? $group->userGroupTranslations->first()?->name ?? $group->id;
                return $group;
            })
            ->sortBy('display_name');
    }

    public function render()
    {
        return view('livewire.manager.user-detail', [
            'userGroups' => $this->userGroups,
        ])->layout('components.layouts.manager');
    }
}

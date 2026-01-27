<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\HandlesMediaUploads;
use App\Livewire\Traits\RequiresAuthentication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    use HandlesMediaUploads;
    use RequiresAuthentication;

    public $name = '';
    public $email = '';
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    // 别名属性，用于兼容视图中的 $avatar 和 $avatarUrl
    public $avatar = null;
    public ?string $avatarUrl = null;

    public function mount()
    {
        $this->ensureAuthenticated();
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        if ($user->hasMedia('avatars')) {
            $this->avatarUrl = first_media_url($user, 'avatars', 'thumb');
        }
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'current_password' => 'nullable|required_with:password',
        'password' => 'nullable|string|min:8|confirmed',
        'avatar' => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'name.required' => '请输入姓名',
        'current_password.required_with' => '请输入当前密码',
        'password.min' => '密码至少需要8个字符',
        'password.confirmed' => '两次输入的密码不一致',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $user->name = $this->name;

        // 如果提供了新密码，验证当前密码
        if (! empty($this->password)) {
            if (! Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', '当前密码不正确');

                return;
            }
            $user->password = Hash::make($this->password);
        }

        $user->save();

        // 处理头像上传
        if ($this->avatar) {
            $user->clearMediaCollection('avatars');
            $user->addMedia($this->avatar->getRealPath())
                ->usingName($this->avatar->getClientOriginalName())
                ->usingFileName($this->avatar->getClientOriginalName())
                ->toMediaCollection('avatars');
            $this->avatarUrl = first_media_url($user, 'avatars', 'thumb');
            $this->avatar = null;
        }

        $this->dispatch('flash-message', type: 'success', message: __('app.edit_user_success'));

        // 重置密码字段
        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->avatar = null;
        $user->clearMediaCollection('avatars');
        $this->avatarUrl = null;
    }

    public function render()
    {
        return view('livewire.users.profile')->layout('components.layouts.app');
    }
}

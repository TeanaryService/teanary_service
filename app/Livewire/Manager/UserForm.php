<?php

namespace App\Livewire\Manager;

use App\Models\User;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public ?int $userGroupId = null;
    public string $password = '';
    public string $passwordConfirmation = '';
    public ?string $emailVerifiedAt = null;
    public $avatar;
    public ?string $avatarUrl = null;

    protected array $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'userGroupId' => 'nullable|exists:user_groups,id',
        'password' => 'required|min:8|confirmed',
        'passwordConfirmation' => 'required',
        'emailVerifiedAt' => 'nullable|date',
        'avatar' => 'nullable|image|max:5120',
    ];

    protected array $messages = [
        'name.required' => '名称不能为空',
        'email.required' => '邮箱不能为空',
        'email.email' => '请输入有效的邮箱地址',
        'email.unique' => '该邮箱已被使用',
        'userGroupId.exists' => '选择的用户组不存在',
        'password.required' => '密码不能为空',
        'password.min' => '密码至少8个字符',
        'password.confirmed' => '两次输入的密码不一致',
        'passwordConfirmation.required' => '请确认密码',
        'emailVerifiedAt.date' => '请输入有效的日期时间',
        'avatar.image' => '上传的文件必须是图片',
        'avatar.max' => '图片大小不能超过5MB',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->userId = $id;
            $user = User::findOrFail($id);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->userGroupId = $user->user_group_id;
            $this->emailVerifiedAt = $user->email_verified_at ? $user->email_verified_at->format('Y-m-d\TH:i') : null;
            
            // 获取头像
            if ($user->hasMedia('avatars')) {
                $this->avatarUrl = $user->getFirstMediaUrl('avatars', 'thumb');
            }

            // 更新验证规则，忽略当前记录
            $this->rules['email'] = 'required|email|max:255|unique:users,email,' . $id;
            $this->rules['password'] = 'nullable|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'nullable';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'user_group_id' => $this->userGroupId,
            'email_verified_at' => $this->emailVerifiedAt ? date('Y-m-d H:i:s', strtotime($this->emailVerifiedAt)) : null,
        ];

        // 如果提供了密码，则加密
        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);

            // 处理头像上传
            if ($this->avatar) {
                $user->clearMediaCollection('avatars');
                $user->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
            }

            session()->flash('message', __('app.updated_successfully'));
        } else {
            $user = User::create($data);

            // 处理头像上传
            if ($this->avatar) {
                $user->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.users'));
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $userGroups = \App\Models\UserGroup::with('userGroupTranslations')->get();

        return view('livewire.manager.user-form', [
            'userGroups' => $userGroups,
            'lang' => $lang,
        ])->layout('components.layouts.manager');
    }
}

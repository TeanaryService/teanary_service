<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HandlesMediaUploads;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\User;
use App\Models\UserGroup;
use Livewire\Component;

class UserForm extends Component
{
    use HandlesMediaUploads;
    use HasNavigationRedirect;
    use UsesLocaleCurrency;

    public ?int $userId = null;
    // 别名属性，用于兼容视图中的 $avatar 和 $avatarUrl
    public $avatar = null;
    public ?string $avatarUrl = null;
    public string $name = '';
    public string $email = '';
    public ?int $userGroupId = null;
    public string $password = '';
    public string $passwordConfirmation = '';
    public ?string $emailVerifiedAt = null;

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
                $this->avatarUrl = first_media_url($user, 'avatars', 'thumb');
            }
        }
    }

    public function save()
    {
        // 根据是否是编辑模式动态设置验证规则
        if ($this->userId) {
            // 编辑模式：邮箱需要忽略当前用户，密码可选
            $this->rules['email'] = 'required|email|max:255|unique:users,email,'.$this->userId;
            $this->rules['password'] = 'nullable|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'nullable';
        } else {
            // 创建模式：邮箱必须唯一，密码必填
            $this->rules['email'] = 'required|email|max:255|unique:users,email';
            $this->rules['password'] = 'required|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'required';
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'user_group_id' => $this->userGroupId,
            'email_verified_at' => $this->emailVerifiedAt ? date('Y-m-d H:i:s', strtotime($this->emailVerifiedAt)) : null,
        ];

        // 如果提供了密码，则加密
        if (! empty($this->password)) {
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
                $this->avatarUrl = first_media_url($user, 'avatars', 'thumb');
            }

            $this->flashMessage('updated_successfully');
        } else {
            $user = User::create($data);

            // 处理头像上传
            if ($this->avatar) {
                $user->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
                $this->avatarUrl = first_media_url($user, 'avatars', 'thumb');
            }

            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.users', $this->userId ? 'updated_successfully' : 'created_successfully');
    }

    public function removeAvatar(): void
    {
        $this->avatar = null;

        if (! $this->userId) {
            $this->avatarUrl = null;

            return;
        }

        $user = User::findOrFail($this->userId);
        $user->clearMediaCollection('avatars');
        $this->avatarUrl = null;
    }

    public function render()
    {
        $userGroups = UserGroup::with('userGroupTranslations')->get();

        return view('livewire.manager.user-form', [
            'userGroups' => $userGroups,
            'lang' => $this->getCurrentLanguage(),
        ])->layout('components.layouts.manager');
    }
}

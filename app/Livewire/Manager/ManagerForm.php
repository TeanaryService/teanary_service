<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HandlesMediaUploads;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Manager;
use Livewire\Component;

class ManagerForm extends Component
{
    use HandlesMediaUploads;
    use HasNavigationRedirect;

    public ?int $managerId = null;
    // 别名属性，用于兼容视图中的 $avatar 和 $avatarUrl
    public $avatar = null;
    public ?string $avatarUrl = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public ?string $emailVerifiedAt = null;
    public ?string $token = null;

    protected array $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email|max:255|unique:managers,email',
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
            $this->managerId = $id;
            $manager = Manager::findOrFail($id);
            $this->name = $manager->name;
            $this->email = $manager->email;
            $this->emailVerifiedAt = $manager->email_verified_at ? $manager->email_verified_at->format('Y-m-d\TH:i') : null;
            $this->token = $manager->token;

            // 获取头像
            if ($manager->hasMedia('avatars')) {
                $this->avatarUrl = first_media_url($manager, 'avatars', 'thumb');
            }
        }
    }

    public function generateToken(): void
    {
        if (! $this->managerId) {
            $this->dispatch('flash-message', type: 'error', message: '请先保存管理员信息');

            return;
        }

        $manager = Manager::findOrFail($this->managerId);
        $token = bin2hex(random_bytes(32));
        $manager->update(['token' => $token]);
        $this->token = $token;
        $this->dispatch('flash-message', type: 'success', message: 'Token已生成: '.$token);
    }

    public function save()
    {
        // 根据是否是编辑模式动态设置验证规则
        if ($this->managerId) {
            // 编辑模式：邮箱需要忽略当前管理员，密码可选
            $this->rules['email'] = 'required|email|max:255|unique:managers,email,'.$this->managerId;
            $this->rules['password'] = 'nullable|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'nullable';
        } else {
            // 创建模式：邮箱必须唯一，密码必填
            $this->rules['email'] = 'required|email|max:255|unique:managers,email';
            $this->rules['password'] = 'required|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'required';
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt ? date('Y-m-d H:i:s', strtotime($this->emailVerifiedAt)) : null,
        ];

        // 如果提供了密码，则加密
        if (! empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->managerId) {
            $manager = Manager::findOrFail($this->managerId);
            $manager->update($data);

            // 处理头像上传
            if ($this->avatar) {
                $manager->clearMediaCollection('avatars');
                $manager->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
                $this->avatarUrl = first_media_url($manager, 'avatars', 'thumb');
            }

            $this->flashMessage('updated_successfully');
        } else {
            $manager = Manager::create($data);

            // 处理头像上传
            if ($this->avatar) {
                $manager->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
                $this->avatarUrl = first_media_url($manager, 'avatars', 'thumb');
            }

            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.managers', $this->managerId ? 'updated_successfully' : 'created_successfully');
    }

    public function removeAvatar(): void
    {
        $this->avatar = null;

        if (! $this->managerId) {
            $this->avatarUrl = null;

            return;
        }

        $manager = Manager::findOrFail($this->managerId);
        $manager->clearMediaCollection('avatars');
        $this->avatarUrl = null;
    }

    public function render()
    {
        return view('livewire.manager.manager-form')->layout('components.layouts.manager');
    }
}

<?php

namespace App\Livewire\Manager;

use App\Models\Manager;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManagerForm extends Component
{
    use WithFileUploads;

    public ?int $managerId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public ?string $emailVerifiedAt = null;
    public ?string $token = null;
    public $avatar;
    public ?string $avatarUrl = null;

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
                $this->avatarUrl = $manager->getFirstMediaUrl('avatars', 'thumb');
            }

            // 更新验证规则，忽略当前记录
            $this->rules['email'] = 'required|email|max:255|unique:managers,email,' . $id;
            $this->rules['password'] = 'nullable|min:8|confirmed';
            $this->rules['passwordConfirmation'] = 'nullable';
        }
    }

    public function generateToken(): void
    {
        if (!$this->managerId) {
            session()->flash('error', '请先保存管理员信息');
            return;
        }
        
        $manager = Manager::findOrFail($this->managerId);
        $token = bin2hex(random_bytes(32));
        $manager->update(['token' => $token]);
        $this->token = $token;
            session()->flash('message', 'Token已生成: ' . $token);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt ? date('Y-m-d H:i:s', strtotime($this->emailVerifiedAt)) : null,
        ];

        // 如果提供了密码，则加密
        if (!empty($this->password)) {
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
            }

            session()->flash('message', __('app.updated_successfully'));
        } else {
            $manager = Manager::create($data);

            // 处理头像上传
            if ($this->avatar) {
                $manager->addMedia($this->avatar->getRealPath())
                    ->toMediaCollection('avatars');
                $this->avatar = null;
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.managers'), navigate: true);
    }

    public function render()
    {
        return view('livewire.manager.manager-form')->layout('components.layouts.manager');
    }
}

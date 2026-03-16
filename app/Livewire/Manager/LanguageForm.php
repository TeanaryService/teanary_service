<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Language;
use Livewire\Component;

class LanguageForm extends Component
{
    use HasNavigationRedirect;
    public ?int $languageId = null;
    public string $code = '';
    public string $name = '';
    public bool $default = false;

    protected array $rules = [
        'code' => 'required|max:10|unique:languages,code',
        'name' => 'required|max:255',
        'default' => 'boolean',
    ];

    protected array $messages = [
        'code.required' => '语言代码不能为空',
        'code.max' => '语言代码不能超过10个字符',
        'code.unique' => '该语言代码已存在',
        'name.required' => '语言名称不能为空',
        'name.max' => '语言名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->languageId = $id;
            $language = Language::findOrFail($id);
            $this->code = $language->code;
            $this->name = $language->name;
            $this->default = $language->default;
        }
    }

    public function updatedDefault($value): void
    {
        if ($value) {
            // 如果设置为默认，取消其他语言的默认状态
            Language::where('id', '!=', $this->languageId)->update(['default' => false]);
        }
    }

    public function save()
    {
        // 根据是否是编辑模式动态设置验证规则
        if ($this->languageId) {
            // 编辑模式：语言代码需要忽略当前语言
            $this->rules['code'] = 'required|max:10|unique:languages,code,'.$this->languageId;
        } else {
            // 创建模式：语言代码必须唯一
            $this->rules['code'] = 'required|max:10|unique:languages,code';
        }

        $this->validate();

        $data = [
            // 'code' => strtolower($this->code),
            'code' => $this->code,
            'name' => $this->name,
            'default' => $this->default,
        ];

        if ($this->default) {
            // 如果设置为默认，取消其他语言的默认状态
            Language::where('id', '!=', $this->languageId)->update(['default' => false]);
        }

        if ($this->languageId) {
            $language = Language::findOrFail($this->languageId);
            $language->update($data);
            $this->flashMessage('updated_successfully');
        } else {
            Language::create($data);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.languages', $this->languageId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        return view('livewire.manager.language-form')->layout('components.layouts.manager');
    }
}

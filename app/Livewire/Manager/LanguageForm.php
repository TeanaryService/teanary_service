<?php

namespace App\Livewire\Manager;

use App\Models\Language;
use Livewire\Component;

class LanguageForm extends Component
{
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
            
            // 更新验证规则，忽略当前记录
            $this->rules['code'] = 'required|max:10|unique:languages,code,' . $id;
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
        $this->validate();

        $data = [
            'code' => strtolower($this->code),
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
            session()->flash('message', __('app.updated_successfully'));
        } else {
            Language::create($data);
            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.languages'), navigate: true);
    }

    public function render()
    {
        return view('livewire.manager.language-form')->layout('components.layouts.manager');
    }
}

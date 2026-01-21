<?php

namespace App\Livewire\Manager;

use App\Models\Currency;
use Livewire\Component;

class CurrencyForm extends Component
{
    public ?int $currencyId = null;
    public string $code = '';
    public string $name = '';
    public string $symbol = '';
    public float $exchangeRate = 1.0000;
    public bool $default = false;

    protected array $rules = [
        'code' => 'required|max:10|unique:currencies,code',
        'name' => 'required|max:255',
        'symbol' => 'required|max:10',
        'exchangeRate' => 'required|numeric|min:0',
        'default' => 'boolean',
    ];

    protected array $messages = [
        'code.required' => '货币代码不能为空',
        'code.max' => '货币代码不能超过10个字符',
        'code.unique' => '该货币代码已存在',
        'name.required' => '货币名称不能为空',
        'name.max' => '货币名称不能超过255个字符',
        'symbol.required' => '货币符号不能为空',
        'symbol.max' => '货币符号不能超过10个字符',
        'exchangeRate.required' => '汇率不能为空',
        'exchangeRate.numeric' => '汇率必须是数字',
        'exchangeRate.min' => '汇率不能小于0',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->currencyId = $id;
            $currency = Currency::findOrFail($id);
            $this->code = $currency->code;
            $this->name = $currency->name;
            $this->symbol = $currency->symbol;
            $this->exchangeRate = $currency->exchange_rate;
            $this->default = $currency->default;
            
            // 更新验证规则，忽略当前记录
            $this->rules['code'] = 'required|max:10|unique:currencies,code,' . $id;
        }
    }

    public function updatedDefault($value): void
    {
        if ($value) {
            // 如果设置为默认，取消其他货币的默认状态
            Currency::where('id', '!=', $this->currencyId)->update(['default' => false]);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'symbol' => $this->symbol,
            'exchange_rate' => $this->exchangeRate,
            'default' => $this->default,
        ];

        if ($this->default) {
            // 如果设置为默认，取消其他货币的默认状态
            Currency::where('id', '!=', $this->currencyId)->update(['default' => false]);
        }

        if ($this->currencyId) {
            $currency = Currency::findOrFail($this->currencyId);
            $currency->update($data);
            session()->flash('message', __('app.updated_successfully'));
        } else {
            Currency::create($data);
            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.currencies'));
    }

    public function render()
    {
        return view('livewire.manager.currency-form')->layout('components.layouts.manager');
    }
}

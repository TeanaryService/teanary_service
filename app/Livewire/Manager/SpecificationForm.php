<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use Livewire\Component;

class SpecificationForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;

    public ?int $specificationId = null;

    protected array $rules = [
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '规格名称不能为空',
        'translations.*.name.max' => '规格名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->initializeTranslationStatus();

        if ($id) {
            $this->specificationId = $id;
            $spec = Specification::with('specificationTranslations')->findOrFail($id);
            $this->translationStatus = $spec->translation_status->value;
            $this->initializeTranslations($spec, 'specificationTranslations', ['name']);
        } else {
            $this->initializeTranslations(null, 'specificationTranslations', ['name']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的规格名称不能为空')) {
            return;
        }

        $this->validate();

        $data = [
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->specificationId) {
            $spec = Specification::findOrFail($this->specificationId);
            $spec->update($data);
            $this->saveTranslations($spec, SpecificationTranslation::class, 'specification_id', ['name']);
            $this->flashMessage('updated_successfully');
        } else {
            $spec = Specification::create($data);
            $this->saveTranslations($spec, SpecificationTranslation::class, 'specification_id', ['name']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.specifications', $this->specificationId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        return view('livewire.manager.specification-form', [
            'languages' => $this->getLanguages(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

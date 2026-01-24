<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoryDetail extends Component
{
    use WithFileUploads;

    public ?Category $category = null;
    public $slug = '';
    public $parentId = null;
    public $translationStatus;
    public $image;
    public $translations = [];

    protected LocaleCurrencyService $localeService;

    protected $rules = [
        'slug' => 'required|string|max:255|unique:categories,slug',
        'parentId' => 'nullable|exists:categories,id',
        'translationStatus' => 'required',
        'image' => 'nullable|image|max:2048',
        'translations.*.name' => 'nullable|string|max:255',
    ];

    public function mount($category = null): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
        
        if ($category) {
            $this->category = Category::with('categoryTranslations')->findOrFail($category);
            $this->slug = $this->category->slug;
            $this->parentId = $this->category->parent_id;
            $this->translationStatus = $this->category->translation_status->value;
            
            // 加载翻译
            $languages = $this->localeService->getLanguages();
            foreach ($languages as $language) {
                $translation = $this->category->categoryTranslations
                    ->where('language_id', $language->id)
                    ->first();
                $this->translations[$language->id] = [
                    'name' => $translation?->name ?? '',
                    'description' => $translation?->description ?? '',
                ];
            }
        } else {
            // 新建模式，初始化翻译数组
            $languages = $this->localeService->getLanguages();
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'name' => '',
                    'description' => '',
                ];
            }
            $this->translationStatus = TranslationStatusEnum::NotTranslated->value;
        }
    }

    public function updatedSlug($value): void
    {
        if ($this->category) {
            $this->rules['slug'] = 'required|string|max:255|unique:categories,slug,' . $this->category->id;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'parent_id' => $this->parentId,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->category) {
            // 更新
            $this->category->update($data);
            $category = $this->category;
        } else {
            // 创建
            $category = Category::create($data);
        }

        // 处理图片上传
        if ($this->image) {
            $category->clearMediaCollection('image');
            $category->addMedia($this->image->getRealPath())
                ->usingName($this->image->getClientOriginalName())
                ->usingFileName($this->image->getClientOriginalName())
                ->toMediaCollection('image');
            $this->image = null;
        }

        // 保存翻译
        foreach ($this->translations as $languageId => $fields) {
            if (!empty($fields['name'])) {
                $category->categoryTranslations()->updateOrCreate(
                    ['language_id' => $languageId],
                    [
                        'name' => $fields['name'],
                        'description' => $fields['description'] ?? '',
                    ]
                );
            }
        }

        session()->flash('message', __('app.save_success'));
        return redirect()->route('manager.categories');
    }

    public function getParentCategoriesProperty()
    {
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        return Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->when($this->category, fn($q) => $q->where('id', '!=', $this->category->id))
            ->get()
            ->map(function ($cat) use ($lang) {
                $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
                $cat->display_name = $translation?->name ?? $cat->categoryTranslations->first()?->name ?? $cat->slug;
                return $cat;
            })
            ->sortBy('display_name');
    }

    public function getLanguagesProperty()
    {
        return $this->localeService->getLanguages();
    }

    public function getTranslationStatusOptionsProperty(): array
    {
        return TranslationStatusEnum::options();
    }

    public function render()
    {
        return view('livewire.manager.category-detail', [
            'parentCategories' => $this->parentCategories,
            'languages' => $this->languages,
            'translationStatusOptions' => $this->translationStatusOptions,
        ])->layout('components.layouts.manager');
    }
}

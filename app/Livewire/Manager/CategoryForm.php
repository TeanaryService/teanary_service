<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoryForm extends Component
{
    use WithFileUploads;

    public ?int $categoryId = null;
    public ?int $parentId = null;
    public string $slug = '';
    public string $translationStatus = '';
    public $image;
    public ?string $imageUrl = null;
    public array $translations = [];

    protected array $rules = [
        'parentId' => 'nullable|exists:categories,id',
        'slug' => 'required|max:255|unique:categories,slug',
        'translationStatus' => 'required',
        'image' => 'nullable|image|max:5120',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'parentId.exists' => '选择的父分类不存在',
        'slug.required' => 'URL别名不能为空',
        'slug.max' => 'URL别名不能超过255个字符',
        'slug.unique' => '该URL别名已存在',
        'translationStatus.required' => '翻译状态不能为空',
        'image.image' => '上传的文件必须是图片',
        'image.max' => '图片大小不能超过5MB',
        'translations.*.name.required' => '分类名称不能为空',
        'translations.*.name.max' => '分类名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->categoryId = $id;
            $category = Category::with('categoryTranslations')->findOrFail($id);
            $this->parentId = $category->parent_id;
            $this->slug = $category->slug;
            $this->translationStatus = $category->translation_status->value;

            // 获取图片
            if ($category->hasMedia('image')) {
                $this->imageUrl = $category->getFirstMediaUrl('image', 'thumb');
            }

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $category->categoryTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'name' => $translation ? $translation->name : '',
                ];
            }

            // 更新验证规则，忽略当前记录
            $this->rules['slug'] = 'required|max:255|unique:categories,slug,'.$id;
            // 不能选择自己作为父分类
            $this->rules['parentId'] = 'nullable|exists:categories,id|not_in:'.$id;
        } else {
            // 初始化翻译数组
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'name' => '',
                ];
            }
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getLanguages()->firstWhere('default', true);
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['name'])) {
            $this->addError('translations.'.$defaultLanguage->id.'.name', '默认语言的分类名称不能为空');

            return;
        }

        // 验证不能选择自己作为父分类
        if ($this->categoryId && $this->parentId == $this->categoryId) {
            $this->addError('parentId', '不能选择自己作为父分类');

            return;
        }

        $this->validate();

        $data = [
            'parent_id' => $this->parentId,
            'slug' => $this->slug,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update($data);

            // 处理图片上传
            if ($this->image) {
                $category->clearMediaCollection('image');
                $category->addMedia($this->image->getRealPath())
                    ->toMediaCollection('image');
                $this->image = null;
            }

            // 更新翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    CategoryTranslation::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $translation['name'],
                        ]
                    );
                }
            }

            Cache::forget(\App\Support\CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
            session()->flash('message', __('app.updated_successfully'));
        } else {
            $category = Category::create($data);

            // 处理图片上传
            if ($this->image) {
                $category->addMedia($this->image->getRealPath())
                    ->toMediaCollection('image');
                $this->image = null;
            }

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    CategoryTranslation::create([
                        'category_id' => $category->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            Cache::forget(\App\Support\CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.categories'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        // 获取所有一级分类（parent_id 为 null，且不是当前分类）
        $parentCategories = Category::with('categoryTranslations')
            ->whereNull('parent_id')
            ->when($this->categoryId, fn ($q) => $q->where('id', '!=', $this->categoryId))
            ->get();

        return view('livewire.manager.category-form', [
            'languages' => $languages,
            'parentCategories' => $parentCategories,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesMediaUploads;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CategoryForm extends Component
{
    use HandlesMediaUploads;
    use HandlesTranslations;
    use HasNavigationRedirect;
    use UsesLocaleCurrency;

    public ?int $categoryId = null;
    public ?int $parentId = null;
    public string $slug = '';

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
        $this->initializeTranslationStatus();

        if ($id) {
            $this->categoryId = $id;
            $category = Category::with('categoryTranslations')->findOrFail($id);
            $this->parentId = $category->parent_id;
            $this->slug = $category->slug;
            $this->translationStatus = $category->translation_status->value;

            // 获取图片
            $this->loadImageUrl($category);

            // 加载翻译
            $this->initializeTranslations($category, 'categoryTranslations');
        } else {
            // 初始化翻译数组
            $this->initializeTranslations(null, 'categoryTranslations');
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        if (! $this->validateDefaultLanguage('name', '默认语言的分类名称不能为空')) {
            return;
        }

        // 根据是否是编辑模式动态设置验证规则
        if ($this->categoryId) {
            // 编辑模式：slug 唯一但忽略当前记录，且不能选择自己作为父分类
            $this->rules['slug'] = 'required|max:255|unique:categories,slug,'.$this->categoryId;
            $this->rules['parentId'] = 'nullable|exists:categories,id|not_in:'.$this->categoryId;
        } else {
            // 创建模式：slug 必须唯一，父分类只做存在性校验
            $this->rules['slug'] = 'required|max:255|unique:categories,slug';
            $this->rules['parentId'] = 'nullable|exists:categories,id';
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
            $this->saveImage($category, 'image', true);

            // 更新翻译
            $this->saveTranslations($category, CategoryTranslation::class, 'category_id');

            Cache::forget(CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
            $this->flashMessage('updated_successfully');
        } else {
            $category = Category::create($data);

            // 处理图片上传
            $this->saveImage($category);

            // 创建翻译
            $this->saveTranslations($category, CategoryTranslation::class, 'category_id');

            Cache::forget(CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.categories', 'created_successfully');
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->imageUrl = null;

        if (! $this->categoryId) {
            return;
        }

        $category = Category::findOrFail($this->categoryId);
        $category->clearMediaCollection('image');
        $this->imageUrl = null;
    }

    public function render()
    {
        $languages = $this->getLanguages();
        $lang = $this->getCurrentLanguage();

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

<?php

namespace App\Livewire\Manager;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;
    use HasTranslatedNames;
    use UsesLocaleCurrency;
    use WithFileUploads;

    public ?int $productId = null;

    public string $slug = '';
    public string $status = '';
    public ?string $sourceUrl = null;

    /** @var array<int, array{attribute_id: int|null, attribute_value_id: int|null}> */
    public array $attributeValues = [];

    /** @var int[] */
    public array $categoryIds = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $newImages = [];

    public array $rules = [
        'slug' => 'required|string|max:255',
        'status' => 'required',
        'translationStatus' => 'required',
        'sourceUrl' => 'nullable|url|max:255',
        'attributeValues.*.attribute_id' => 'nullable|integer',
        'attributeValues.*.attribute_value_id' => 'nullable|integer',
        'categoryIds' => 'array',
        'categoryIds.*' => 'integer',
        'translations.*.name' => 'nullable|string|max:255',
        'translations.*.short_description' => 'nullable|string',
        'translations.*.description' => 'nullable|string',
        'newImages.*' => 'image|max:2048',
    ];

    public array $messages = [
        'slug.required' => 'Slug 不能为空',
        'status.required' => '商品状态不能为空',
        'translationStatus.required' => '翻译状态不能为空',
        'sourceUrl.url' => '来源链接格式不正确',
        'translations.*.name.max' => '商品名称不能超过255个字符',
        'newImages.*.image' => '上传的文件必须是图片',
        'newImages.*.max' => '图片大小不能超过 2MB',
    ];

    public function mount(?int $id = null): void
    {
        $this->status = ProductStatusEnum::Active->value;
        $this->initializeTranslationStatus();

        if ($id) {
            $this->productId = $id;
            $product = Product::with(['productTranslations', 'productCategories', 'attributeValues'])->findOrFail($id);

            $this->slug = $product->slug;
            $this->status = $product->status->value;
            $this->translationStatus = $product->translation_status->value;
            $this->sourceUrl = $product->source_url;

            $this->categoryIds = $product->productCategories->pluck('id')->all();
            $this->initializeTranslations($product, 'productTranslations', ['name', 'short_description', 'description']);

            // 初始化属性值行（简单按照当前关联生成）
            $this->attributeValues = [];
            foreach ($product->attributeValues as $av) {
                $this->attributeValues[] = [
                    'attribute_id' => $av->pivot->attribute_id ?? $av->attribute_id ?? null,
                    'attribute_value_id' => $av->id,
                ];
            }
        } else {
            $this->initializeTranslations(null, 'productTranslations', ['name', 'short_description', 'description']);

            $this->attributeValues = [
                [
                    'attribute_id' => null,
                    'attribute_value_id' => null,
                ],
            ];
        }
    }

    public function addAttributeValueRow(): void
    {
        $this->attributeValues[] = [
            'attribute_id' => null,
            'attribute_value_id' => null,
        ];
    }

    public function removeAttributeValueRow(int $index): void
    {
        unset($this->attributeValues[$index]);
        $this->attributeValues = array_values($this->attributeValues);
    }

    public function updatedNewImages(): void
    {
        if (empty($this->newImages)) {
            return;
        }

        // 仅校验图片本身（不触发表单全量校验）
        $this->validate(
            ['newImages.*' => $this->rules['newImages.*']],
            $this->messages
        );

        // 编辑已有商品时：选择文件后立即入库并刷新缩略图
        if ($this->productId) {
            $product = Product::findOrFail($this->productId);

            foreach ($this->newImages as $upload) {
                $product
                    ->addMedia($upload->getRealPath())
                    ->usingFileName($upload->getClientOriginalName())
                    ->toMediaCollection('images');
            }

            // 清空临时文件，避免重复上传
            $this->newImages = [];
        }
    }

    public function save()
    {
        $rules = $this->rules;
        $rules['slug'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('products', 'slug')->ignore($this->productId),
        ];

        $this->validate($rules, $this->messages);

        $data = [
            'slug' => $this->slug,
            'status' => ProductStatusEnum::from($this->status),
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
            'source_url' => $this->sourceUrl,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            $this->flashMessage('updated_successfully');
        } else {
            $product = Product::create($data);
            $this->productId = $product->id;
            $this->flashMessage('created_successfully');
        }

        // 同步分类
        $product->syncProductCategories($this->categoryIds);

        // 同步属性值
        $syncAttributeValues = [];
        foreach ($this->attributeValues as $row) {
            if (! empty($row['attribute_id']) && ! empty($row['attribute_value_id'])) {
                $attributeId = (int) $row['attribute_id'];
                $attributeValueId = (int) $row['attribute_value_id'];
                $syncAttributeValues[$attributeValueId] = ['attribute_id' => $attributeId];
            }
        }
        if (! empty($syncAttributeValues)) {
            $product->syncAttributeValues($syncAttributeValues);
        }

        // 同步翻译
        $this->saveTranslations($product, ProductTranslation::class, 'product_id', ['name', 'short_description', 'description']);

        // 处理图片（追加）
        if (! empty($this->newImages)) {
            foreach ($this->newImages as $upload) {
                $product
                    ->addMedia($upload->getRealPath())
                    ->usingFileName($upload->getClientOriginalName())
                    ->toMediaCollection('images');
            }
            $this->newImages = [];
        }

        return $this->redirectWithMessage('manager.products', $this->productId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        $service = $this->getLocaleService();
        $lang = $this->getCurrentLanguage();

        $attributes = Attribute::with('attributeTranslations')->get();
        $attributeOptions = [];
        foreach ($attributes as $attr) {
            $attributeOptions[$attr->id] = $this->translatedField(
                $attr->attributeTranslations,
                $lang,
                'name',
                (string) $attr->id
            );
        }

        $attributeValueOptions = [];
        $allAttrValues = AttributeValue::with('attributeValueTranslations')->get();
        foreach ($allAttrValues as $av) {
            $name = $this->translatedField($av->attributeValueTranslations, $lang, 'name', (string) $av->id);
            $attributeValueOptions[$av->attribute_id][$av->id] = $name;
        }

        $categories = \App\Models\Category::with('categoryTranslations')->get()->map(function ($cat) use ($service) {
            $lang = $this->getCurrentLanguage();
            return [
                'id' => $cat->id,
                'name' => $this->translatedField($cat->categoryTranslations, $lang, 'name', (string) $cat->id),
            ];
        });

        $existingImages = [];
        if ($this->productId) {
            $product = Product::with('media')->find($this->productId);
            $existingImages = $product?->getMedia('images') ?? [];
        }

        return view('livewire.manager.product-form', [
            'languages' => $this->getLanguages(),
            'statusOptions' => ProductStatusEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
            'attributes' => $attributes,
            'attributeOptions' => $attributeOptions,
            'attributeValueOptions' => $attributeValueOptions,
            'categories' => $categories,
            'existingImages' => $existingImages,
        ])->layout('components.layouts.manager');
    }
}

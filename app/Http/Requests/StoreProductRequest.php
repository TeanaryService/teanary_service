<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|unique:products,slug',
            'main_image' => 'nullable|array',
            'main_image.image_id' => 'required_with:main_image|string',
            'main_image.contents' => 'required_with:main_image|string',
            'content_images' => 'nullable|array',
            'content_images.*.image_id' => 'required|string',
            'content_images.*.original_url' => 'required|string',
            'content_images.*.contents' => 'required|string',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|integer|exists:languages,id',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.short_description' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*.slug' => 'required|string',
            'categories.*.parent_id' => 'nullable|integer|exists:categories,id',
            'categories.*.translations' => 'nullable|array',
            'categories.*.translations.*.language_id' => 'required|integer|exists:languages,id',
            'categories.*.translations.*.name' => 'required|string|max:255',
            'categories.*.translations.*.description' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.cost' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.specification_values' => 'nullable|array',
            'variants.*.specification_values.*.specification_id' => 'required|integer|exists:specifications,id',
            'variants.*.specification_values.*.specification_value_id' => 'required|integer|exists:specification_values,id',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'URL别名不能为空',
            'slug.unique' => 'URL别名已存在',
            'translations.required' => '至少需要一种语言的翻译',
            'translations.*.language_id.required' => '语言ID不能为空',
            'translations.*.language_id.exists' => '指定的语言不存在',
            'translations.*.name.required' => '商品名称不能为空',
            'categories.*.slug.required' => '分类slug不能为空',
            'categories.*.translations.*.language_id.required' => '分类翻译的语言ID不能为空',
            'categories.*.translations.*.name.required' => '分类名称不能为空',
            'variants.*.sku.required' => 'SKU不能为空',
            'variants.*.sku.unique' => 'SKU已存在',
            'variants.*.specification_values.*.specification_id.required' => '规格ID不能为空',
            'variants.*.specification_values.*.specification_value_id.required' => '规格值ID不能为空',
        ];
    }
}

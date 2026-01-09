<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 准备验证数据，去掉 source_url 的查询参数.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('source_url') && $this->source_url !== null && $this->source_url !== '') {
            $url = $this->source_url;
            // 去掉查询参数（? 后面的部分）和锚点（# 后面的部分）
            $questionMarkPos = strpos($url, '?');
            if ($questionMarkPos !== false) {
                $url = substr($url, 0, $questionMarkPos);
            }
            
            $this->merge([
                'source_url' => $url,
            ]);
        }
    }

    /**
     * 验证失败时返回JSON响应.
     */
    protected function failedValidation(Validator $validator)
    {
        // 直接记录原始请求数据和验证错误
        $errors = $validator->errors()->toArray();
        
        Log::warning('商品上传验证失败', [
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'validation_errors' => $errors,
            'request_data' => $this->all(),
        ]);

        throw new HttpResponseException(
            response()->json([
                'message' => '验证失败',
                'errors' => $errors,
            ], 422)
        );
    }

    public function rules(): array
    {
        $rules = [
            'slug' => 'required|string|unique:products,slug',
            'source_url' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                    $fail('source_url必须是有效的URL地址');
                }
            }],
            'main_image' => 'nullable|array',
            'main_images' => 'nullable|array',
        ];

        // 只有当 main_image 存在且不为 null 时才验证其字段
        if ($this->filled('main_image')) {
            $rules['main_image.image_id'] = 'required|string';
            $rules['main_image.contents'] = 'required_without:main_image.image_url|nullable|string';
            $rules['main_image.image_url'] = 'required_without:main_image.contents|nullable|url';
        }

        // 只有当 main_images 存在且不为空时才验证其字段
        if ($this->filled('main_images') && is_array($this->main_images) && count($this->main_images) > 0) {
            $rules['main_images.*.image_id'] = 'required|string';
            $rules['main_images.*.image_url'] = 'required_without:main_images.*.contents|nullable|url';
            $rules['main_images.*.contents'] = 'required_without:main_images.*.image_url|nullable|string';
        }

        $rules['content_images'] = 'nullable|array';
        $rules['content_images.*.image_id'] = 'required|string';
        $rules['content_images.*.image_url'] = 'required_without:content_images.*.contents|url';
        $rules['content_images.*.contents'] = 'required_without:content_images.*.image_url|string';
        $rules['translations'] = 'required|array|min:1';
        $rules['translations.*.language_id'] = 'required|integer|exists:languages,id';
        $rules['translations.*.name'] = 'required|string|max:255';
        $rules['translations.*.description'] = 'nullable|string';
        $rules['translations.*.short_description'] = 'nullable|string';
        $rules['categories'] = 'nullable|array';
        $rules['categories.*.slug'] = 'required|string';
        $rules['categories.*.parent_id'] = 'nullable|integer|exists:categories,id';
        $rules['categories.*.translations'] = 'nullable|array';
        $rules['categories.*.translations.*.language_id'] = 'required|integer|exists:languages,id';
        $rules['categories.*.translations.*.name'] = 'required|string|max:255';
        $rules['categories.*.translations.*.description'] = 'nullable|string';
        $rules['variants'] = 'nullable|array';
        $rules['variants.*.sku'] = 'required|string|unique:product_variants,sku';
        $rules['variants.*.price'] = 'nullable|numeric|min:0';
        $rules['variants.*.cost'] = 'nullable|numeric|min:0';
        $rules['variants.*.stock'] = 'nullable|integer|min:0';
        $rules['variants.*.weight'] = 'nullable|numeric|min:0';
        $rules['variants.*.length'] = 'nullable|numeric|min:0';
        $rules['variants.*.width'] = 'nullable|numeric|min:0';
        $rules['variants.*.height'] = 'nullable|numeric|min:0';
        $rules['variants.*.specification_values'] = 'nullable|array';
        // 只支持通过名称创建规格（第三方采集的商品没有ID）
        $rules['variants.*.specification_values.*.specification_name'] = 'required|string|max:255';
        $rules['variants.*.specification_values.*.specification_value_name'] = 'required|string|max:255';
        $rules['attributes'] = 'nullable|array';
        $rules['attributes.*.name'] = 'required|string|max:255';
        $rules['attributes.*.value'] = 'required|string|max:255';

        return $rules;
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
            'variants.*.specification_values.*.specification_name.required' => '规格名称不能为空',
            'variants.*.specification_values.*.specification_value_name.required' => '规格值名称不能为空',
            'attributes.*.name.required' => '属性名称不能为空',
            'attributes.*.value.required' => '属性值不能为空',
        ];
    }
}

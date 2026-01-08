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
     * 验证失败时返回JSON响应.
     */
    protected function failedValidation(Validator $validator)
    {
        // 记录验证失败的详细信息
        $errors = $validator->errors()->toArray();
        $requestData = $this->all();

        // 排除大的图片内容，避免日志过大
        $logData = [
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'validation_errors' => $errors,
            'request_data' => [
                'slug' => $requestData['slug'] ?? null,
                'source_url' => $requestData['source_url'] ?? null,
                'main_image' => isset($requestData['main_image']) ? [
                    'image_id' => $requestData['main_image']['image_id'] ?? null,
                    'has_contents' => isset($requestData['main_image']['contents']),
                    'has_image_url' => isset($requestData['main_image']['image_url']),
                ] : null,
                'content_images_count' => isset($requestData['content_images']) ? count($requestData['content_images']) : 0,
                'translations_count' => isset($requestData['translations']) ? count($requestData['translations']) : 0,
                'translations' => isset($requestData['translations']) ? array_map(function ($trans) {
                    return [
                        'language_id' => $trans['language_id'] ?? null,
                        'name' => $trans['name'] ?? null,
                        'has_description' => isset($trans['description']),
                        'has_short_description' => isset($trans['short_description']),
                    ];
                }, $requestData['translations']) : [],
                'categories_count' => isset($requestData['categories']) ? count($requestData['categories']) : 0,
                'variants_count' => isset($requestData['variants']) ? count($requestData['variants']) : 0,
                'variants' => isset($requestData['variants']) ? array_map(function ($variant) {
                    return [
                        'sku' => $variant['sku'] ?? null,
                        'price' => $variant['price'] ?? null,
                        'stock' => $variant['stock'] ?? null,
                        'has_specification_values' => isset($variant['specification_values']),
                        'specification_values_count' => isset($variant['specification_values']) ? count($variant['specification_values']) : 0,
                    ];
                }, $requestData['variants']) : [],
                'attributes_count' => isset($requestData['attributes']) ? count($requestData['attributes']) : 0,
                'attributes' => isset($requestData['attributes']) ? array_map(function ($attr) {
                    return [
                        'name' => $attr['name'] ?? null,
                        'value' => $attr['value'] ?? null,
                    ];
                }, $requestData['attributes']) : [],
            ],
        ];

        Log::warning('商品上传验证失败', $logData);

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

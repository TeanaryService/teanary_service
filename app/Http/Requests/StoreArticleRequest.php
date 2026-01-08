<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreArticleRequest extends FormRequest
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
        throw new HttpResponseException(
            response()->json([
                'message' => '验证失败',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function rules(): array
    {
        return [
            'article_id' => 'nullable|integer',
            'slug' => 'required|string|unique:articles,slug',
            'main_image' => 'nullable|array',
            'main_image.image_id' => 'required_with:main_image|string',
            'main_image.contents' => 'required_without:main_image.image_url|string',
            'main_image.image_url' => 'required_without:main_image.contents|url',
            'content_images' => 'nullable|array',
            'content_images.*.image_id' => 'required|string',
            'content_images.*.image_url' => 'required_without:content_images.*.contents|url',
            'content_images.*.contents' => 'required_without:content_images.*.image_url|string',
            'translations' => 'required|array|min:1',
            'translations.*.language_id' => 'required|integer|exists:languages,id',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.content' => 'nullable|string',
            'translations.*.summary' => 'nullable|string',
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
            'translations.*.title.required' => '文章标题不能为空',
        ];
    }
}

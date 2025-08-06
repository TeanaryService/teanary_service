<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|unique:articles,slug',
            'is_published' => 'boolean',
            'translations' => 'required|array',
            'translations.*.language_id' => 'required|int|exists:languages,id',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.summary' => 'nullable|string',
            'translations.*.content' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'slug是必填项',
            'slug.unique' => 'slug已存在',
            'translations.required' => '翻译数据是必填项',
            'translations.*.language_id.required' => '语言ID是必填项',
            'translations.*.language_id.exists' => '语言ID不存在',
            'translations.*.title.required' => '标题是必填项',
        ];
    }
}

<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * 提供删除操作功能的 Trait.
 *
 * 用于需要删除模型实例的组件
 */
trait HasDeleteAction
{
    /**
     * 删除模型实例.
     *
     * @param  string  $modelClass  模型类名（如 Product::class）
     * @param  int  $id  模型 ID
     * @param  string|null  $cacheKey  需要清除的缓存键（可选）
     * @param  string  $messageKey  成功消息键（默认 'deleted_successfully'）
     */
    protected function deleteModel(string $modelClass, int $id, ?string $cacheKey = null, string $messageKey = 'deleted_successfully'): void
    {
        $model = $modelClass::findOrFail($id);
        $model->delete();

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        session()->flash('message', __("app.{$messageKey}"));
    }
}

<?php

namespace App\Livewire\Traits;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * 提供媒体上传处理功能的 Trait.
 *
 * 用于需要处理图片/文件上传的组件
 */
trait HandlesMediaUploads
{
    use WithFileUploads;

    /**
     * 上传的图片文件.
     *
     * @var TemporaryUploadedFile|null
     */
    public $image = null;

    /**
     * 图片 URL（用于显示现有图片）.
     */
    public ?string $imageUrl = null;

    /**
     * 加载图片 URL.
     *
     * @param  object|null  $model  模型实例
     * @param  string  $collection  媒体集合名称（默认 'image'）
     * @param  string  $conversion  转换名称（默认 'thumb'）
     */
    protected function loadImageUrl(?object $model, string $collection = 'image', string $conversion = 'thumb'): void
    {
        if ($model && method_exists($model, 'hasMedia') && $model->hasMedia($collection)) {
            $this->imageUrl = first_media_url($model, $collection, $conversion);
        }
    }

    /**
     * 保存上传的图片.
     *
     * @param  object  $model  模型实例
     * @param  string  $collection  媒体集合名称（默认 'image'）
     * @param  bool  $clearExisting  是否清除现有媒体（默认 false）
     */
    protected function saveImage(object $model, string $collection = 'image', bool $clearExisting = false): void
    {
        if ($this->image) {
            if ($clearExisting) {
                $model->clearMediaCollection($collection);
            }
            $model->addMedia($this->image->getRealPath())
                ->toMediaCollection($collection);
            $this->image = null;
        }
    }

    /**
     * 清除图片相关属性.
     */
    protected function clearImage(): void
    {
        $this->image = null;
        $this->imageUrl = null;
    }
}

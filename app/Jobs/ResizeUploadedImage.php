<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ResizeUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 如果模型不存在，自动删除任务而不抛出异常
     */
    public $deleteWhenMissingModels = true;

    public function __construct(public Media $media) {}

    /**
     * 处理任务失败的情况（模型不存在）
     */
    public function failed(\Throwable $exception): void
    {
        // 如果是因为模型不存在导致的失败，记录警告但不抛出异常
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            Log::warning('图片调整任务失败：Media 模型不存在', [
                'media_id' => $this->media->id ?? null,
                'error' => $exception->getMessage(),
            ]);
            return;
        }

        // 其他错误记录错误日志
        Log::error('图片调整任务失败', [
            'media_id' => $this->media->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }

    public function handle(): void
    {
        // 检查模型是否仍然存在（可能在队列等待期间被删除）
        if (!$this->media->exists) {
            Log::warning('图片调整任务跳过：Media 模型不存在', [
                'media_id' => $this->media->id ?? null,
            ]);
            return;
        }

        // 重新加载模型以确保数据是最新的
        $this->media->refresh();

        $path = $this->media->getPath();

        if (! file_exists($path)) {
            Log::warning('图片调整任务跳过：文件不存在', [
                'media_id' => $this->media->id,
                'path' => $path,
            ]);
            return;
        }

        try {
            $image = Image::load($path);

            $width = $image->getWidth();
            $height = $image->getHeight();

            // 如果宽和高都小于等于 800，不处理
            if ($width <= 800 && $height <= 800) {
                return;
            }

            // 按比例缩放：长边为 800
            if ($width >= $height) {
                $image->width(800, [Constraint::PreserveAspectRatio]);
            } else {
                $image->height(800, [Constraint::PreserveAspectRatio]);
            }

            // 保存处理图到临时路径
            $image->save($path);
            Log::info('图片调整完成', ['path' => $path]);
        } catch (\Exception $e) {
            Log::error('图片调整失败', [
                'media_id' => $this->media->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            // 不重新抛出异常，避免任务重试
        }
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ResizeUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 如果模型不存在，自动删除任务而不抛出异常.
     */
    public $deleteWhenMissingModels = true;

    public function __construct(public Media $media) {}

    /**
     * 处理任务失败的情况（模型不存在）.
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
        if (! $this->media->exists) {
            Log::warning('图片调整任务跳过：Media 模型不存在', [
                'media_id' => $this->media->id ?? null,
            ]);

            return;
        }

        // 重新加载模型以确保数据是最新的
        $this->media->refresh();

        // 使用 PathGeneratorFactory 获取正确的相对路径
        $pathGeneratorFactory = app(\Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory::class);
        $pathGenerator = $pathGeneratorFactory->create($this->media);
        $directory = $pathGenerator->getPath($this->media);
        $fileName = $this->media->file_name ?? $this->media->name ?? 'file';
        $path = rtrim($directory, '/').'/'.$fileName;
        
        $disk = $this->media->disk ?? config('media-library.disk_name', 'public');
        $diskInstance = Storage::disk($disk);

        // 使用 Storage facade 检查文件是否存在（支持不同的 disk）
        if (! $diskInstance->exists($path)) {
            Log::warning('图片调整任务跳过：文件不存在', [
                'media_id' => $this->media->id,
                'path' => $path,
                'disk' => $disk,
            ]);

            return;
        }

        try {
            // 对于本地磁盘，直接加载
            if ($disk === 'local' || $disk === 'public') {
                $image = Image::load($diskInstance->path($path));
                $tempPath = null; // 不需要临时文件
            } else {
                // 对于远程磁盘（如 S3），下载到临时文件
                $tempPath = sys_get_temp_dir().'/'.uniqid('resize_').'_'.basename($path);
                file_put_contents($tempPath, $diskInstance->get($path));
                $image = Image::load($tempPath);
            }

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

            // 保存处理后的图片
            if ($disk === 'local' || $disk === 'public') {
                $image->save($diskInstance->path($path));
            } else {
                // 对于远程磁盘，保存到临时文件后上传
                $image->save($tempPath);
                $diskInstance->put($path, file_get_contents($tempPath));
            }
            
            // 清理临时文件（如果存在）
            if (isset($tempPath) && $tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            
            Log::info('图片调整完成', ['path' => $path, 'disk' => $disk]);
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

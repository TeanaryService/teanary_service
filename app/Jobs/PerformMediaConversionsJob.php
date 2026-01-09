<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob as BasePerformConversionsJob;

/**
 * 包装 Spatie 的 PerformConversionsJob，在执行时禁用 Media 同步
 * 防止转换过程中更新 Media 模型时触发同步导致死循环
 */
class PerformMediaConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 如果模型不存在，自动删除任务而不抛出异常.
     */
    public $deleteWhenMissingModels = true;

    public function __construct(
        protected ConversionCollection $conversions,
        protected Media $media
    ) {}

    /**
     * 执行任务
     */
    public function handle(): void
    {
        // 禁用 Media 同步，防止转换过程中更新 Media 模型时触发同步
        $wasDisabled = \App\Observers\MediaObserver::$syncDisabled;
        \App\Observers\MediaObserver::$syncDisabled = true;

        try {
            // 使用 Spatie 的原始 Job 来处理转换
            // PerformConversionsJob 的 handle 方法需要 FileManipulator 参数
            $fileManipulator = app(\Spatie\MediaLibrary\Conversions\FileManipulator::class);
            $baseJob = new BasePerformConversionsJob($this->conversions, $this->media);
            $baseJob->handle($fileManipulator);
        } finally {
            // 恢复之前的同步状态
            \App\Observers\MediaObserver::$syncDisabled = $wasDisabled;
        }
    }

    /**
     * 处理任务失败的情况
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Media 转换任务失败', [
            'media_id' => $this->media->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}

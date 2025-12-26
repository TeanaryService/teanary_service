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

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        $path = $this->media->getPath();

        if (! file_exists($path)) {
            return;
        }

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
        Log::info($path);
    }
}

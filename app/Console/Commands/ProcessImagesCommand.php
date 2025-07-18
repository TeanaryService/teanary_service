<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessImagesCommand extends Command
{
    protected $signature = 'app:process-images';

    protected $description = '把demo图片处理成680x680';

    public function handle()
    {
        ini_set('memory_limit', '512M');
        
        $disk = Storage::disk('local');
        $folder = 'demo';
        
        // 检查目录是否存在
        if (!$disk->exists($folder)) {
            $this->error("Directory {$folder} does not exist.");
            return 1;
        }

        $files = $disk->files($folder);
        
        if (empty($files)) {
            $this->info("No files found in {$folder} directory.");
            return 0;
        }

        $manager = new ImageManager(new Driver());
        $processedCount = 0;

        foreach ($files as $file) {
            if (!$this->isImage($file)) {
                continue;
            }
            
            try {
                $this->info("Processing {$file}...");

                $path = $disk->path($file);
                
                // 检查文件是否存在
                if (!file_exists($path)) {
                    $this->warn("File {$path} does not exist, skipping.");
                    continue;
                }

                $img = $manager->read($path);

                // 生成新的 jpg 路径
                $pathInfo = pathinfo($file);
                $newRelativePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.jpg';
                // 处理根目录情况
                if ($pathInfo['dirname'] === '.') {
                    $newRelativePath = $pathInfo['filename'] . '.jpg';
                }
                
                $newPath = $disk->path($newRelativePath);

                // 获取原始尺寸
                $width = $img->width();
                $height = $img->height();

                if ($width <= 0 || $height <= 0) {
                    $this->warn("Invalid image dimensions for {$file}, skipping.");
                    continue;
                }

                // 按比例缩放，使短边等于680
                $minSide = min($width, $height);
                $scale = 680 / $minSide;
                $newWidth = (int)round($width * $scale);
                $newHeight = (int)round($height * $scale);

                // 缩放图片
                $img->resize($newWidth, $newHeight);

                // 居中裁剪到 680x680
                $img->crop(680, 680);

                // 保存图片
                $img->save($newPath);

                // 如果原文件不是 jpg 格式，删除原文件
                $originalExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($originalExt !== 'jpg') {
                    $disk->delete($file);
                    $this->info("Converted and deleted original file: {$file}");
                } else {
                    $this->info("Processed existing JPG file: {$file}");
                }

                $processedCount++;

            } catch (\Exception $e) {
                $this->error("Error processing {$file}: " . $e->getMessage());
                continue;
            }
        }

        // 最终清理：删除所有剩余的非jpg文件
        $this->info('Performing final cleanup...');
        $filesAfter = $disk->files($folder);
        $deletedCount = 0;
        
        foreach ($filesAfter as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg'])) {
                try {
                    $disk->delete($file);
                    $this->info("Deleted leftover file: {$file}");
                    $deletedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete {$file}: " . $e->getMessage());
                }
            }
        }

        $this->info("Processing completed! Processed: {$processedCount} images, Deleted: {$deletedCount} non-JPG files.");
        return 0;
    }

    protected function isImage($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg']);
    }
}
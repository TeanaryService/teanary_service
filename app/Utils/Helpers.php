<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('isImage')) {
    function isImage($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }
}

if (!function_exists('generateRandomImage')) {
    function generateRandomImage()
    {
        $disk = Storage::disk('local');
        $folder = 'demo';

        $files = $disk->files($folder);

        $images = array_filter($files, function ($file) {
            return isImage($file);
        });

        if (empty($images)) {
            return null;
        }

        return $disk->path($images[array_rand($images)]);
    }
}
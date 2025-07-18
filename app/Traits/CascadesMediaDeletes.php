<?php

namespace App\Traits;

trait CascadesMediaDeletes
{
    protected static function bootCascadesMediaDeletes()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'media')) {
                // 逐个删除 media
                foreach ($model->media as $media) {
                    $media->delete();
                }
            }
        });
    }
}

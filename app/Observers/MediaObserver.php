<?php

namespace App\Observers;

use App\Services\SyncService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    /**
     * 静态属性，用于临时禁用同步
     */
    public static bool $syncDisabled = false;

    /**
     * Handle the Media "created" event.
     */
    public function created(Media $media): void
    {
        if (config('sync.enabled') && !static::$syncDisabled) {
            app(SyncService::class)->recordSync($media, 'created', config('sync.node'));
        }
    }

    /**
     * Handle the Media "updated" event.
     */
    public function updated(Media $media): void
    {
        if (config('sync.enabled') && !static::$syncDisabled) {
            app(SyncService::class)->recordSync($media, 'updated', config('sync.node'));
        }
    }

    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        if (config('sync.enabled') && !static::$syncDisabled) {
            app(SyncService::class)->recordSync($media, 'deleted', config('sync.node'));
        }
    }
}

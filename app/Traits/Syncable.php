<?php

namespace App\Traits;

use App\Services\SyncService;

trait Syncable
{
    /**
     * 静态属性，用于临时禁用同步
     */
    public static bool $syncDisabled = false;

    /**
     * Boot the trait
     */
    protected static function bootSyncable(): void
    {
        // 监听模型事件
        static::created(function ($model) {
            if (!static::$syncDisabled) {
                app(SyncService::class)->recordSync($model, 'created', config('sync.node'));
            }
        });

        static::updated(function ($model) {
            if (!static::$syncDisabled) {
                app(SyncService::class)->recordSync($model, 'updated', config('sync.node'));
            }
        });

        static::deleted(function ($model) {
            if (!static::$syncDisabled) {
                app(SyncService::class)->recordSync($model, 'deleted', config('sync.node'));
            }
        });
    }
}

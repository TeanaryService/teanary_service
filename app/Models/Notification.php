<?php

namespace App\Models;

use App\Services\SnowflakeService;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;
use Illuminate\Support\Facades\App;

class Notification extends LaravelDatabaseNotification
{
    use HasSnowflakeId;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    /**
     * 在创建时确保使用雪花ID
     * 注意：HasSnowflakeId trait 会在 creating 事件中生成ID
     * 但 Laravel 的 DatabaseNotification 可能会先设置一个字符串ID
     * 我们需要确保最终使用雪花ID（整数）
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // 在 creating 事件中确保使用雪花ID
        // 使用优先级确保在 HasSnowflakeId trait 之后执行
        static::creating(function ($notification) {
            $keyName = $notification->getKeyName();
            // 强制使用雪花ID，无论之前是否已设置
            // 这样可以覆盖 Laravel 的 DatabaseNotification 可能设置的字符串ID
            $notification->{$keyName} = App::make(SnowflakeService::class)->nextId();
        }, 100); // 使用高优先级确保最后执行
    }
}

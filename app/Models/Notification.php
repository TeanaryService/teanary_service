<?php

namespace App\Models;

use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;

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
}

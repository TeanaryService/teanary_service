<?php

namespace App\Models;

use App\Traits\HasSnowflakeId;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * 自定义 Media 模型，支持雪花ID.
 */
class Media extends BaseMedia
{
    use HasSnowflakeId;

    /**
     * 获取主键类型.
     */
    public function getKeyType(): string
    {
        return 'int';
    }

    /**
     * 获取主键是否自增.
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}

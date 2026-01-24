<?php

namespace App\Traits;

use App\Services\SnowflakeService;
use Illuminate\Support\Facades\App;

/**
 * 雪花ID Trait.
 *
 * 为模型提供雪花ID支持
 */
trait HasSnowflakeId
{
    /**
     * Boot the trait.
     */
    public static function bootHasSnowflakeId(): void
    {
        // 在创建模型前生成ID
        static::creating(function ($model) {
            $keyName = $model->getKeyName();
            // 使用更严格的检查：只有当 id 为 null 或未设置时才生成
            $keyValue = $model->getAttribute($keyName);
            if ($keyValue === null) {
                $model->{$keyName} = App::make(SnowflakeService::class)->nextId();
            }
        });
    }

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

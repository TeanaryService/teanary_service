<?php

namespace App\Traits;

use App\Services\SnowflakeService;
use Illuminate\Support\Facades\App;

/**
 * 雪花ID Trait
 * 
 * 为模型提供雪花ID支持
 */
trait HasSnowflakeId
{
    /**
     * Boot the trait
     */
    public static function bootHasSnowflakeId(): void
    {
        // 在创建模型前生成ID
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = App::make(SnowflakeService::class)->nextId();
            }
        });
    }

    /**
     * 获取主键类型
     */
    public function getKeyType(): string
    {
        return 'int';
    }

    /**
     * 获取主键是否自增
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}

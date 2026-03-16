<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 级联删除 Trait.
 *
 * 用于在代码层面管理关联删除，替代数据库外键约束
 * 这样可以避免多节点同步时的外键约束问题
 */
trait CascadesDeletes
{
    /**
     * 定义需要级联删除的关联关系
     * 子类可以重写此方法来自定义级联删除行为.
     *
     * @return array 关联关系数组，格式: ['relationName' => 'delete|null|ignore']
     */
    protected function getCascadeDeletes(): array
    {
        return [];
    }

    /**
     * Boot the trait.
     */
    protected static function bootCascadesDeletes(): void
    {
        static::deleting(function ($model) {
            $cascadeDeletes = $model->getCascadeDeletes();

            foreach ($cascadeDeletes as $relation => $action) {
                if (! method_exists($model, $relation)) {
                    continue;
                }

                $related = $model->$relation();

                switch ($action) {
                    case 'delete':
                        // 级联删除关联记录
                        if ($related instanceof HasMany) {
                            $related->get()->each(function ($item) {
                                $item->delete();
                            });
                        } elseif ($related instanceof BelongsToMany) {
                            $related->detach();
                        } elseif ($related instanceof HasOne) {
                            $related->delete();
                        }
                        break;

                    case 'null':
                        // 将外键设置为 null
                        if ($related instanceof HasMany) {
                            $foreignKey = $related->getForeignKeyName();
                            $related->update([$foreignKey => null]);
                        }
                        break;

                    case 'ignore':
                        // 忽略，不做任何操作
                        break;
                }
            }
        });
    }
}

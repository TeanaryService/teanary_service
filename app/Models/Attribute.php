<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\AttributeObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Attribute
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|AttributeTranslation[] $attributeTranslations
 * @property Collection|AttributeValue[] $attributeValues
 */
#[ObservedBy([AttributeObserver::class])]

class Attribute extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    public function attributeTranslations(): HasMany
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * 获取所有属性及属性值（多语言），永久缓存
     *
     * @param  int|null  $langId
     * @return \Illuminate\Support\Collection
     */
    public static function getCachedAttributes()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('attributes.with.translations', function () {
            return static::with([
                'attributeTranslations',
                'attributeValues.attributeValueTranslations',
            ])->get();
        });
    }

    /**
     * 获取当前语言下的属性及属性值
     */
    public static function getAttributesForLanguage($langId)
    {
        $attributes = static::getCachedAttributes();

        return $attributes->map(function ($attr) use ($langId) {
            $trans = $attr->attributeTranslations->where('language_id', $langId)->first();

            return [
                'id' => $attr->id,
                'name' => $trans ? $trans->name : $attr->id,
                'values' => $attr->attributeValues->map(function ($val) use ($langId) {
                    $valTrans = $val->attributeValueTranslations->where('language_id', $langId)->first();

                    return [
                        'id' => $val->id,
                        'name' => $valTrans ? $valTrans->name : $val->id,
                    ];
                })->values(),
            ];
        });
    }
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
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
 * 
 * @property Collection|AttributeTranslation[] $attributeTranslations
 * @property Collection|AttributeValue[] $attributeValues
 *
 * @package App\Models
 */
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
     * @param int|null $langId
     * @return \Illuminate\Support\Collection
     */
    public static function getCachedAttributes(?int $langId = null)
    {
        $langId = $langId ?: app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        return \Illuminate\Support\Facades\Cache::rememberForever("attributes.with.translations.{$langId}", function () use ($langId) {
            return static::with([
                'attributeTranslations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                },
                'attributeValues.attributeValueTranslations' => function ($q) use ($langId) {
                    $q->where('language_id', $langId);
                }
            ])->get()->map(function ($attr) use ($langId) {
                $trans = $attr->attributeTranslations->first();
                return [
                    'id' => $attr->id,
                    'name' => $trans ? $trans->name : $attr->id,
                    'values' => $attr->attributeValues->map(function ($val) use ($langId) {
                        $valTrans = $val->attributeValueTranslations->first();
                        return [
                            'id' => $val->id,
                            'name' => $valTrans ? $valTrans->name : $val->id,
                        ];
                    })->values(),
                ];
            });
        });
    }
}

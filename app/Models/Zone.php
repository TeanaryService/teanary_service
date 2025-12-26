<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\ZoneObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Zone
 *
 * @property int $id
 * @property int $country_id
 * @property string|null $code
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Country $country
 * @property Collection|Address[] $addresses
 * @property Collection|ZoneTranslation[] $zoneTranslations
 */
#[ObservedBy([ZoneObserver::class])]

class Zone extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'country_id' => 'int',
        'active' => 'bool',
    ];

    protected $fillable = [
        'country_id',
        'code',
        'active',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function zoneTranslations(): HasMany
    {
        return $this->hasMany(ZoneTranslation::class);
    }

    /**
     * 获取所有地区数据缓存(包含所有语言)
     */
    public static function getCachedZones()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('zones.with.translations', function () {
            return static::with('zoneTranslations')
                ->get()
                ->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'country_id' => $zone->country_id,
                        'translations' => $zone->zoneTranslations
                            ->mapWithKeys(function ($trans) {
                                return [$trans->language_id => $trans->name];
                            })->toArray(),
                        'default_name' => $zone->name,
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    /**
     * 从缓存获取指定国家和语言的地区列表
     */
    public static function getZonesByCountryAndLanguage(int $countryId, ?int $langId = null)
    {
        $zones = self::getCachedZones();
        $langId = $langId ?: app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        return collect($zones)
            ->where('country_id', $countryId)
            ->map(function ($zone) use ($langId) {
                return [
                    'id' => $zone['id'],
                    'name' => $zone['translations'][$langId] ?? $zone['default_name'],
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }
}

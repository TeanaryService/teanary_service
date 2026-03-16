<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\TranslationStatusEnum;
use App\Observers\ZoneObserver;
use App\Services\LocaleCurrencyService;
use App\Support\CacheKeys;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Class Zone.
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
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'country_id' => 'int',
        'active' => 'bool',
        'translation_status' => TranslationStatusEnum::class,
    ];

    protected $fillable = [
        'country_id',
        'code',
        'active',
        'translation_status',
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
     * 获取指定国家的地区数据缓存（按国家缓存，避免单条缓存过大超过 MySQL max_allowed_packet）.
     *
     * @return array<int, array{id: int, country_id: int, translations: array<int, string>, default_name: string}>
     */
    public static function getCachedZonesForCountry(int $countryId): array
    {
        $key = CacheKeys::ZONES_BY_COUNTRY_PREFIX.$countryId;

        return Cache::rememberForever($key, function () use ($countryId) {
            return static::with('zoneTranslations')
                ->where('country_id', $countryId)
                ->get()
                ->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'country_id' => $zone->country_id,
                        'translations' => $zone->zoneTranslations
                            ->mapWithKeys(function ($trans) {
                                return [$trans->language_id => $trans->name];
                            })->toArray(),
                        'default_name' => $zone->name ?? $zone->zoneTranslations->first()?->name ?? '',
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    /**
     * 从缓存获取指定国家和语言的地区列表.
     */
    public static function getZonesByCountryAndLanguage(int $countryId, ?int $langId = null): array
    {
        $zones = self::getCachedZonesForCountry($countryId);
        $langId = $langId ?: app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        return collect($zones)
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

    /**
     * 清除指定国家的地区缓存（Zone/ZoneTranslation 变更时调用）.
     */
    public static function clearZoneCacheForCountry(int $countryId): void
    {
        Cache::forget(CacheKeys::ZONES_BY_COUNTRY_PREFIX.$countryId);
    }
}

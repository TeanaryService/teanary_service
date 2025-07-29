<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\CountryObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Country
 * 
 * @property int $id
 * @property string|null $iso_code_2
 * @property string|null $iso_code_3
 * @property bool $postcode_required
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Address[] $addresses
 * @property Collection|CountryTranslation[] $countryTranslations
 * @property Collection|Zone[] $zones
 *
 * @package App\Models
 */

#[ObservedBy([CountryObserver::class])]

class Country extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'postcode_required' => 'boolean',
        'active' => 'bool'
    ];

    protected $fillable = [
        'iso_code_2',
        'iso_code_3',
        'postcode_required',
        'active'
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function countryTranslations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    /**
     * 获取所有国家数据缓存(包含所有语言)
     */
    public static function getCachedCountries()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("countries.with.translations", function () {
            return static::with('countryTranslations')
                ->get()
                ->map(function($country) {
                    return [
                        'id' => $country->id,
                        'translations' => $country->countryTranslations
                            ->mapWithKeys(function($trans) {
                                return [$trans->language_id => $trans->name];
                            })->toArray(),
                        'default_name' => $country->name
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    /**
     * 从缓存获取指定语言的国家列表
     */
    public static function getCountriesByLanguage(?int $langId = null)
    {
        $countries = self::getCachedCountries();
        $langId = $langId ?: app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;

        return collect($countries)->map(function($country) use ($langId) {
            return [
                'id' => $country['id'],
                'name' => $country['translations'][$langId] ?? $country['default_name']
            ];
        })->sortBy('name')->values()->toArray();
    }
}

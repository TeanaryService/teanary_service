<?php

namespace App\Observers;

use App\Models\Zone;
use App\Models\ZoneTranslation;

class ZoneTranslationObserver
{
    /**
     * 地区翻译变更时，清除该地区所属国家的地区缓存.
     */
    public function created(ZoneTranslation $translation): void
    {
        $this->clearZoneCacheForTranslation($translation);
    }

    public function updated(ZoneTranslation $translation): void
    {
        $this->clearZoneCacheForTranslation($translation);
    }

    public function deleted(ZoneTranslation $translation): void
    {
        $this->clearZoneCacheForTranslation($translation);
    }

    protected function clearZoneCacheForTranslation(ZoneTranslation $translation): void
    {
        $countryId = $translation->zone_id
            ? (Zone::where('id', $translation->zone_id)->value('country_id'))
            : null;
        if ($countryId !== null) {
            Zone::clearZoneCacheForCountry((int) $countryId);
        }
    }
}

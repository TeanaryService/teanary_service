<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
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
 * 
 * @property Country $country
 * @property Collection|Address[] $addresses
 * @property Collection|ZoneTranslation[] $zoneTranslations
 *
 * @package App\Models
 */
class Zone extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'country_id' => 'int',
        'active' => 'bool'
    ];

    protected $fillable = [
        'country_id',
        'code',
        'active'
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
}

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
class Country extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'postcode_required' => 'bool',
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
}

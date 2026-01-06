<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CountryTranslation.
 *
 * @property int $id
 * @property int $country_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Country $country
 * @property Language $language
 */
class CountryTranslation extends Model
{
    use HasFactory;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'country_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'country_id',
        'language_id',
        'name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

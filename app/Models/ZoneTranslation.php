<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ZoneTranslation
 * 
 * @property int $id
 * @property int $zone_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Language $language
 * @property Zone $zone
 *
 * @package App\Models
 */
class ZoneTranslation extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'zone_id' => 'int',
        'language_id' => 'int'
    ];

    protected $fillable = [
        'zone_id',
        'language_id',
        'name'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}

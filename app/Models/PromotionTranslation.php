<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Syncable;

/**
 * Class PromotionTranslation.
 *
 * @property int $id
 * @property int $promotion_id
 * @property int $language_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Language $language
 * @property Promotion $promotion
 */
class PromotionTranslation extends Model
{
    use HasFactory;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'promotion_id',
        'language_id',
        'name',
        'description',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}

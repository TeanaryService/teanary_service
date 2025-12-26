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
 * Class AttributeTranslation
 *
 * @property int $id
 * @property int $attribute_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Attribute $attribute
 * @property Language $language
 */
class AttributeTranslation extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'attribute_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'attribute_id',
        'language_id',
        'name',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

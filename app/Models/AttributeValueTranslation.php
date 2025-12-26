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
 * Class AttributeValueTranslation
 *
 * @property int $id
 * @property int $attribute_value_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property AttributeValue $attributeValue
 * @property Language $language
 */
class AttributeValueTranslation extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'attribute_value_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'attribute_value_id',
        'language_id',
        'name',
    ];

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

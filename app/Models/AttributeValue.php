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
 * Class AttributeValue
 * 
 * @property int $id
 * @property int $attribute_id
 * @property string|null $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Attribute $attribute
 * @property Collection|AttributeValueTranslation[] $attributeValueTranslations
 * @property Collection|ProductVariantValue[] $productVariantValues
 *
 * @package App\Models
 */
class AttributeValue extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'attribute_id' => 'int'
    ];

    protected $fillable = [
        'attribute_id',
        'code'
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValueTranslations(): HasMany
    {
        return $this->hasMany(AttributeValueTranslation::class);
    }

    public function productVariantValues(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class);
    }
}

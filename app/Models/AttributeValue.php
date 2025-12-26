<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\AttributeValueObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class AttributeValue.
 *
 * @property int $id
 * @property int $attribute_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Attribute $attribute
 * @property Collection|AttributeValueTranslation[] $attributeValueTranslations
 * @property Collection|Product[] $products
 */
#[ObservedBy([AttributeValueObserver::class])]

class AttributeValue extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'attribute_id' => 'int',
    ];

    protected $fillable = [
        'attribute_id',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValueTranslations(): HasMany
    {
        return $this->hasMany(AttributeValueTranslation::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value')
            ->withPivot('attribute_id');
    }
}

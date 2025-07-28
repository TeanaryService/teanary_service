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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SpecificationValue
 * 
 * @property int $id
 * @property int $specification_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Specification $specification
 * @property Collection|ProductVariant[] $productVariants
 * @property Collection|SpecificationValueTranslation[] $specificationValueTranslations
 *
 * @package App\Models
 */
class SpecificationValue extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'specification_id' => 'int'
    ];

    protected $fillable = [
        'specification_id'
    ];

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_specification_value')
            ->withPivot('specification_id');
    }

    public function specificationValueTranslations(): HasMany
    {
        return $this->hasMany(SpecificationValueTranslation::class);
    }
}

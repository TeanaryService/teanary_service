<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\TranslationStatusEnum;
use App\Observers\SpecificationObserver;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Specification.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|ProductVariant[] $productVariants
 * @property Collection|SpecificationTranslation[] $specificationTranslations
 * @property Collection|SpecificationValue[] $specificationValues
 */
#[ObservedBy([SpecificationObserver::class])]
class Specification extends Model
{
    use HasFactory;
    use HasSnowflakeId;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'translation_status' => TranslationStatusEnum::class,
    ];

    protected $fillable = [
        'translation_status',
    ];

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_specification_value')
            ->withPivot('specification_value_id')
            ->using(ProductVariantSpecificationValue::class);
    }

    public function specificationTranslations(): HasMany
    {
        return $this->hasMany(SpecificationTranslation::class);
    }

    public function specificationValues(): HasMany
    {
        return $this->hasMany(SpecificationValue::class);
    }
}

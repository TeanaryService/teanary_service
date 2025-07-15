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
 * Class Specification
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|SpecificationTranslation[] $specificationTranslations
 * @property Collection|SpecificationValue[] $specificationValues
 *
 * @package App\Models
 */
class Specification extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    public function specificationTranslations(): HasMany
    {
        return $this->hasMany(SpecificationTranslation::class);
    }

    public function specificationValues(): HasMany
    {
        return $this->hasMany(SpecificationValue::class);
    }
}

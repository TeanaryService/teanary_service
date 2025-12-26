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
 * Class SpecificationValueTranslation.
 *
 * @property int $id
 * @property int $specification_value_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Language $language
 * @property SpecificationValue $specificationValue
 */
class SpecificationValueTranslation extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'specification_value_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'specification_value_id',
        'language_id',
        'name',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function specificationValue(): BelongsTo
    {
        return $this->belongsTo(SpecificationValue::class);
    }
}

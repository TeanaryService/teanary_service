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
 * Class SpecificationTranslation
 *
 * @property int $id
 * @property int $specification_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Language $language
 * @property Specification $specification
 */
class SpecificationTranslation extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'specification_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'specification_id',
        'language_id',
        'name',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }
}

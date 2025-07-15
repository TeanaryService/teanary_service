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
 * Class Attribute
 * 
 * @property int $id
 * @property string $code
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AttributeTranslation[] $attributeTranslations
 * @property Collection|AttributeValue[] $attributeValues
 *
 * @package App\Models
 */
class Attribute extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $fillable = [
        'code',
        'type'
    ];

    public function attributeTranslations(): HasMany
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}

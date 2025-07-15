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
 * Class ShippingMethodTranslation
 * 
 * @property int $id
 * @property int $shipping_method_id
 * @property int $language_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Language $language
 * @property ShippingMethod $shippingMethod
 *
 * @package App\Models
 */
class ShippingMethodTranslation extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'shipping_method_id' => 'int',
        'language_id' => 'int'
    ];

    protected $fillable = [
        'shipping_method_id',
        'language_id',
        'name',
        'description'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}

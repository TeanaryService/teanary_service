<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\ProductTranslationObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductTranslation
 *
 * @property int $id
 * @property int $product_id
 * @property int $language_id
 * @property string $name
 * @property string|null $short_description
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Language $language
 * @property Product $product
 */
#[ObservedBy([ProductTranslationObserver::class])]

class ProductTranslation extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'product_id',
        'language_id',
        'name',
        'short_description',
        'description',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductCategory
 * 
 * @property int $id
 * @property int $product_id
 * @property int $category_id
 * 
 * @property Category $category
 * @property Product $product
 *
 * @package App\Models
 */
class ProductCategory extends Model
{
    use HasFactory;
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'category_id' => 'int'
    ];

    protected $fillable = [
        'product_id',
        'category_id'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

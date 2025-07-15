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
 * Class CategoryTranslation
 * 
 * @property int $id
 * @property int $category_id
 * @property int $language_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Category $category
 * @property Language $language
 *
 * @package App\Models
 */
class CategoryTranslation extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'category_id' => 'int',
        'language_id' => 'int'
    ];

    protected $fillable = [
        'category_id',
        'language_id',
        'name',
        'description'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

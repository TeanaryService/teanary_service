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
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Article
 * 
 * @property int $id
 * @property string $slug
 * @property bool $is_published
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User|null $user
 * @property Collection|ArticleTranslation[] $articleTranslations
 *
 * @package App\Models
 */
class Article extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'is_published' => 'bool',
        'user_id' => 'int'
    ];

    protected $fillable = [
        'slug',
        'is_published',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function articleTranslations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }
}

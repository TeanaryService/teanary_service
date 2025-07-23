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
 * Class ArticleTranslation
 * 
 * @property int $id
 * @property int $article_id
 * @property string $language_id
 * @property string $title
 * @property string|null $summary
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Article $article
 *
 * @package App\Models
 */
class ArticleTranslation extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'article_id' => 'int'
    ];

    protected $fillable = [
        'article_id',
        'language_id',
        'title',
        'summary',
        'content'
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

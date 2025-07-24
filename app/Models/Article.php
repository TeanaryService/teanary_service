<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\ArticleObserver;
use App\Traits\CascadesMediaDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

#[ObservedBy([ArticleObserver::class])]

class Article extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use CascadesMediaDeletes;
    use Searchable;

    /**
     * 获取模型的索引化数据数组。
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // 合并所有翻译的 title 和 content，并移除 HTML 标签
        $translations = $this->articleTranslations;
        $mergedText = '';
        foreach ($translations as $translation) {
            $mergedText .= strip_tags($translation->title ?? '') . ' ';
            $mergedText .= strip_tags($translation->content ?? '') . ' ';
        }
        $array['content'] = trim($mergedText);

        return $array;
    }


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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(225)
            ->sharpen(7);
    }
}

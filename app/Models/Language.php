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
 * Class Language
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AttributeTranslation[] $attributeTranslations
 * @property Collection|AttributeValueTranslation[] $attributeValueTranslations
 * @property Collection|CategoryTranslation[] $categoryTranslations
 * @property Collection|ProductTranslation[] $productTranslations
 * @property Collection|PromotionTranslation[] $promotionTranslations
 * @property Collection|UserGroupTranslation[] $userGroupTranslations
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Language extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $fillable = [
        'code',
        'name'
    ];

    public function attributeTranslations(): HasMany
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function attributeValueTranslations(): HasMany
    {
        return $this->hasMany(AttributeValueTranslation::class);
    }

    public function categoryTranslations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function productTranslations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function promotionTranslations(): HasMany
    {
        return $this->hasMany(PromotionTranslation::class);
    }

    public function userGroupTranslations(): HasMany
    {
        return $this->hasMany(UserGroupTranslation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'default_language_id');
    }
}

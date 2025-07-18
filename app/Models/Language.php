<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\LanguageObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
 * @property bool $default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|AttributeTranslation[] $attributeTranslations
 * @property Collection|AttributeValueTranslation[] $attributeValueTranslations
 * @property Collection|CategoryTranslation[] $categoryTranslations
 * @property Collection|CountryTranslation[] $countryTranslations
 * @property Collection|PaymentMethodTranslation[] $paymentMethodTranslations
 * @property Collection|ProductTranslation[] $productTranslations
 * @property Collection|PromotionTranslation[] $promotionTranslations
 * @property Collection|ShippingMethodTranslation[] $shippingMethodTranslations
 * @property Collection|SpecificationTranslation[] $specificationTranslations
 * @property Collection|SpecificationValueTranslation[] $specificationValueTranslations
 * @property Collection|UserGroupTranslation[] $userGroupTranslations
 * @property Collection|User[] $users
 * @property Collection|ZoneTranslation[] $zoneTranslations
 *
 * @package App\Models
 */

 #[ObservedBy([LanguageObserver::class])]

class Language extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'default' => 'bool'
    ];

    protected $fillable = [
        'code',
        'name',
        'default'
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

    public function countryTranslations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function paymentMethodTranslations(): HasMany
    {
        return $this->hasMany(PaymentMethodTranslation::class);
    }

    public function productTranslations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function promotionTranslations(): HasMany
    {
        return $this->hasMany(PromotionTranslation::class);
    }

    public function shippingMethodTranslations(): HasMany
    {
        return $this->hasMany(ShippingMethodTranslation::class);
    }

    public function specificationTranslations(): HasMany
    {
        return $this->hasMany(SpecificationTranslation::class);
    }

    public function specificationValueTranslations(): HasMany
    {
        return $this->hasMany(SpecificationValueTranslation::class);
    }

    public function userGroupTranslations(): HasMany
    {
        return $this->hasMany(UserGroupTranslation::class);
    }

    public function zoneTranslations(): HasMany
    {
        return $this->hasMany(ZoneTranslation::class);
    }
}

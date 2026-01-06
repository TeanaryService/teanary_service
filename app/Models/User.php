<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use App\Traits\CascadesMediaDeletes;
use App\Traits\Syncable;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class User.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon|null $email_verified_at
 * @property int|null $user_group_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Currency|null $currency
 * @property Language|null $language
 * @property UserGroup|null $userGroup
 * @property Collection|Address[] $addresses
 * @property Collection|Cart[] $carts
 * @property Collection|Order[] $orders
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia, MustVerifyEmail
{
    use CascadesMediaDeletes;
    use HasFactory, Notifiable;
    use InteractsWithMedia;
    use Syncable;

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    public static $snakeAttributes = false;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_group_id' => 'int',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'user_group_id',
        'remember_token',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl(collectionName: 'avatars', conversionName: 'thumb');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // User 模型不应该访问管理面板，只有 Manager 可以
        // Filament 会通过 authGuard 来区分，但这里明确拒绝更安全
        return false;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->nonOptimized();
    }
}

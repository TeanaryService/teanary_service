<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserGroupTranslation.
 *
 * @property int $id
 * @property int $user_group_id
 * @property int $language_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Language $language
 * @property UserGroup $userGroup
 */
class UserGroupTranslation extends Model
{
    use HasFactory;
    use HasSnowflakeId;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'user_group_id' => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'user_group_id',
        'language_id',
        'name',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }
}

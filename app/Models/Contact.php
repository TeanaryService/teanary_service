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

/**
 * Class Contact.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Contact extends Model
{
    use HasFactory;
    use HasSnowflakeId;
    use Syncable;

    public static $snakeAttributes = false;

    protected $fillable = [
        'name',
        'email',
        'message',
    ];
}

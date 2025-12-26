<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EditorUpload
 *
 * @property int $id
 * @property string $path
 * @property bool $used
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EditorUpload extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'used' => 'bool',
    ];

    protected $fillable = [
        'path',
        'used',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncStatus extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'node',
        'last_synced_at',
        'sync_hash',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    /**
     * 更新或创建同步状态
     */
    public static function updateSyncStatus(
        string $modelType,
        int $modelId,
        string $node,
        string $syncHash
    ): self {
        return static::updateOrCreate(
            [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'node' => $node,
            ],
            [
                'last_synced_at' => now(),
                'sync_hash' => $syncHash,
            ]
        );
    }

    /**
     * 检查是否需要同步（通过哈希值判断数据是否变更）
     */
    public static function needsSync(
        string $modelType,
        int $modelId,
        string $node,
        string $currentHash
    ): bool {
        $status = static::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('node', $node)
            ->first();

        if (!$status) {
            return true; // 从未同步过，需要同步
        }

        return $status->sync_hash !== $currentHash; // 哈希值不同，数据已变更
    }
}

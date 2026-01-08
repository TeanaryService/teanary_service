<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'source_node',
        'target_node',
        'status',
        'payload',
        'error_message',
        'retry_count',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];

    /**
     * 获取待同步的日志.
     */
    public static function getPendingLogs(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * 标记为处理中.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * 标记为完成.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'synced_at' => now(),
        ]);
    }

    /**
     * 标记为失败.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}

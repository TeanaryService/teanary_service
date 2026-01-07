<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Services\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBatchDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;
    public int $batchSize;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $targetNode,
        public int $limit = 50
    ) {
        $this->tries = config('sync.retry_times', 3);
        $this->backoff = config('sync.retry_delay', 60);
        $this->queue = config('sync.queue', 'sync');
        $this->timeout = config('sync.timeout', 600);
        $this->batchSize = config('sync.batch_sync_size', 50); // 每批同步的记录数
    }

    /**
     * Execute the job.
     */
    public function handle(SyncService $syncService): void
    {
        try {
            // 获取待同步的记录（按目标节点分组）
            $pendingLogs = SyncLog::where('status', 'pending')
                ->where('target_node', $this->targetNode)
                ->orderBy('created_at', 'asc')
                ->limit($this->limit)
                ->get();

            if ($pendingLogs->isEmpty()) {
                Log::info('批量同步：没有待同步的记录', [
                    'target_node' => $this->targetNode,
                ]);
                return;
            }

            Log::info('开始批量同步', [
                'target_node' => $this->targetNode,
                'count' => $pendingLogs->count(),
            ]);

            // 分批处理，每批最多 batchSize 条
            $chunks = $pendingLogs->chunk($this->batchSize);
            
            foreach ($chunks as $chunk) {
                $result = $syncService->syncBatchToRemote($chunk, $this->targetNode);
                
                Log::info('批量同步完成', [
                    'target_node' => $this->targetNode,
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'total' => $chunk->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('批量同步任务执行失败', [
                'target_node' => $this->targetNode,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('批量同步任务最终失败', [
            'target_node' => $this->targetNode,
            'error' => $exception->getMessage(),
        ]);
    }
}

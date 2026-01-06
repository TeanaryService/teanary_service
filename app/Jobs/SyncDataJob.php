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

class SyncDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SyncLog $syncLog
    ) {
        $this->tries = config('sync.retry_times', 3);
        $this->backoff = config('sync.retry_delay', 60);
        $this->queue = config('sync.queue', 'sync');
        $this->timeout = config('sync.timeout', 300);
    }

    /**
     * Execute the job.
     */
    public function handle(SyncService $syncService): void
    {
        try {
            $success = $syncService->syncToRemote($this->syncLog);

            if (!$success && $this->syncLog->retry_count < $this->tries) {
                // 重新入队重试
                $this->release($this->backoff);
            }
        } catch (\Exception $e) {
            Log::error('同步任务执行失败', [
                'sync_log_id' => $this->syncLog->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->syncLog->retry_count < $this->tries) {
                $this->release($this->backoff);
            } else {
                $this->syncLog->markAsFailed($e->getMessage());
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('同步任务最终失败', [
            'sync_log_id' => $this->syncLog->id,
            'error' => $exception->getMessage(),
        ]);

        $this->syncLog->markAsFailed($exception->getMessage());
    }
}

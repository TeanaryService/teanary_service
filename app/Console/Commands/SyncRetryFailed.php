<?php

namespace App\Console\Commands;

use App\Jobs\SyncDataJob;
use App\Models\SyncLog;
use Illuminate\Console\Command;

class SyncRetryFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:retry-failed 
                            {--limit=50 : 每次重试的记录数}
                            {--queue : 是否使用队列}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重试失败的同步任务';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('sync.enabled')) {
            $this->error('同步功能未启用，请在 .env 中设置 SYNC_ENABLED=true');
            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $useQueue = $this->option('queue');
        $maxRetries = config('sync.retry_times', 3);

        $this->info("开始重试失败的同步任务（限制: {$limit} 条，最大重试次数: {$maxRetries}）...");

        $failedLogs = SyncLog::where('status', 'failed')
            ->where('retry_count', '<', $maxRetries)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($failedLogs->isEmpty()) {
            $this->info('没有需要重试的失败记录');
            return Command::SUCCESS;
        }

        $this->info("找到 {$failedLogs->count()} 条失败记录需要重试");

        $bar = $this->output->createProgressBar($failedLogs->count());
        $bar->start();

        foreach ($failedLogs as $syncLog) {
            // 重置状态为 pending
            $syncLog->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            if ($useQueue) {
                SyncDataJob::dispatch($syncLog);
            } else {
                // 同步执行
                $syncService = app(\App\Services\SyncService::class);
                $syncService->syncToRemote($syncLog);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('重试任务已提交');

        return Command::SUCCESS;
    }
}

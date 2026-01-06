<?php

namespace App\Console\Commands;

use App\Jobs\SyncDataJob;
use App\Models\SyncLog;
use Illuminate\Console\Command;

class SyncPendingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:pending 
                            {--limit=100 : 每次处理的记录数}
                            {--queue : 是否使用队列}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步待处理的数据到远程节点';

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

        $this->info("开始同步待处理数据（限制: {$limit} 条）...");

        $pendingLogs = SyncLog::getPendingLogs($limit);

        if ($pendingLogs->isEmpty()) {
            $this->info('没有待同步的数据');
            return Command::SUCCESS;
        }

        $this->info("找到 {$pendingLogs->count()} 条待同步记录");

        $bar = $this->output->createProgressBar($pendingLogs->count());
        $bar->start();

        foreach ($pendingLogs as $syncLog) {
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
        $this->info('同步任务已提交');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\SyncBatchDataJob;
use App\Models\SyncLog;
use App\Services\SyncService;
use Illuminate\Console\Command;

class SyncRetryFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-retry-failed 
                            {--limit=50 : 每次重试的记录数}
                            {--queue : 是否使用队列}
                            {--batch-size=50 : 批量同步时每批的记录数}';

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
        if (! config('sync.enabled')) {
            $this->error('同步功能未启用，请在 .env 中设置 SYNC_ENABLED=true');

            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $useQueue = $this->option('queue');
        $batchSize = (int) $this->option('batch-size');
        $maxRetries = config('sync.retry_times', 3);

        $this->info("开始重试失败的同步任务（限制: {$limit} 条，每批: {$batchSize} 条，最大重试次数: {$maxRetries}）...");

        // 获取所有目标节点
        $config = config('sync');
        $currentNode = $config['node'];
        $targetNodes = array_filter(
            array_keys($config['remote_nodes']),
            fn ($node) => $node !== $currentNode
        );

        if (empty($targetNodes)) {
            $this->warn('没有配置目标节点');

            return Command::SUCCESS;
        }

        $totalRetried = 0;

        foreach ($targetNodes as $targetNode) {
            $failedLogs = SyncLog::where('status', 'failed')
                ->where('target_node', $targetNode)
                ->where('retry_count', '<', $maxRetries)
                ->orderBy('created_at', 'asc')
                ->limit($limit)
                ->get();

            if ($failedLogs->isEmpty()) {
                $this->info("节点 {$targetNode}: 没有需要重试的失败记录");
                continue;
            }

            $this->info("节点 {$targetNode}: 找到 {$failedLogs->count()} 条失败记录需要重试");

            // 重置状态为 pending
            $failedLogs->each(function ($syncLog) {
                $syncLog->update([
                    'status' => 'pending',
                    'error_message' => null,
                ]);
            });

            if ($useQueue) {
                // 使用队列异步处理
                SyncBatchDataJob::dispatch($targetNode, $failedLogs->count());
                $this->info("节点 {$targetNode}: 重试任务已加入队列");
            } else {
                // 同步执行批量同步
                $syncService = app(SyncService::class);
                $chunks = $failedLogs->chunk($batchSize);
                $bar = $this->output->createProgressBar($failedLogs->count());
                $bar->start();

                foreach ($chunks as $chunk) {
                    $result = $syncService->syncBatchToRemote($chunk, $targetNode);
                    $totalRetried += $result['success'];
                    $bar->advance($chunk->count());
                }

                $bar->finish();
                $this->newLine();
                $this->info("节点 {$targetNode}: 重试完成");
            }
        }

        if ($useQueue) {
            $this->info('重试任务已提交到队列');
        } else {
            $this->info("重试完成，共同步 {$totalRetried} 条记录");
        }

        return Command::SUCCESS;
    }
}

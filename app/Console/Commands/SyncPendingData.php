<?php

namespace App\Console\Commands;

use App\Jobs\SyncBatchDataJob;
use App\Models\SyncLog;
use App\Services\SyncService;
use Illuminate\Console\Command;

class SyncPendingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-pending 
                            {--limit=100 : 每次处理的记录数}
                            {--queue : 是否使用队列}
                            {--batch-size=50 : 批量同步时每批的记录数}';

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
        if (! config('sync.enabled')) {
            $this->error('同步功能未启用，请在 .env 中设置 SYNC_ENABLED=true');

            return Command::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $useQueue = $this->option('queue');
        $batchSize = (int) $this->option('batch-size');

        return $this->handleBatchSync($limit, $useQueue, $batchSize);
    }

    /**
     * 处理批量同步.
     */
    protected function handleBatchSync(int $limit, bool $useQueue, int $batchSize): int
    {
        $this->info("开始批量同步待处理数据（限制: {$limit} 条，每批: {$batchSize} 条）...");

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

        $totalSynced = 0;

        foreach ($targetNodes as $targetNode) {
            $count = SyncLog::where('status', 'pending')
                ->where('target_node', $targetNode)
                ->count();

            if ($count === 0) {
                $this->info("节点 {$targetNode}: 没有待同步记录");
                continue;
            }

            $this->info("节点 {$targetNode}: 找到 {$count} 条待同步记录");

            if ($useQueue) {
                // 使用队列异步处理
                SyncBatchDataJob::dispatch($targetNode, min($limit, $count));
                $this->info("节点 {$targetNode}: 批量同步任务已加入队列");
            } else {
                // 同步执行
                $syncService = app(SyncService::class);
                $pendingLogs = SyncLog::where('status', 'pending')
                    ->where('target_node', $targetNode)
                    ->orderBy('created_at', 'asc')
                    ->limit($limit)
                    ->get();

                if ($pendingLogs->isEmpty()) {
                    continue;
                }

                $chunks = $pendingLogs->chunk($batchSize);
                $bar = $this->output->createProgressBar($pendingLogs->count());
                $bar->start();

                foreach ($chunks as $chunk) {
                    $result = $syncService->syncBatchToRemote($chunk, $targetNode);
                    $totalSynced += $result['success'];
                    $bar->advance($chunk->count());
                }

                $bar->finish();
                $this->newLine();
                $this->info("节点 {$targetNode}: 批量同步完成");
            }
        }

        if ($useQueue) {
            $this->info('批量同步任务已提交到队列');
        } else {
            $this->info("批量同步完成，共同步 {$totalSynced} 条记录");
        }

        return Command::SUCCESS;
    }
}

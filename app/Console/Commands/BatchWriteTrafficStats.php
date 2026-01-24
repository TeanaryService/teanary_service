<?php

namespace App\Console\Commands;

use App\Jobs\BatchWriteTrafficStatsJob;
use Illuminate\Console\Command;

class BatchWriteTrafficStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:batch-write-traffic-stats {--queue : 是否使用队列异步执行}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '批量写入流量统计数据到数据库';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $useQueue = $this->option('queue');

        if ($useQueue) {
            BatchWriteTrafficStatsJob::dispatch();
            $this->info('流量统计批量写入任务已加入队列');
        } else {
            $job = new BatchWriteTrafficStatsJob;
            $job->handle();
            $this->info('流量统计批量写入完成');
        }

        return Command::SUCCESS;
    }
}

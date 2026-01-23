<?php

namespace App\Console\Commands;

use App\Models\TrafficStatistic;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanOldTrafficStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-traffic-stats {--days=90 : 保留最近多少天的数据}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理超过指定天数的流量统计数据（默认90天）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("开始清理 {$days} 天前的流量统计数据（{$cutoffDate->format('Y-m-d H:i:s')} 之前）...");

        try {
            // 使用 chunk 分批删除，避免一次性删除大量数据导致内存问题
            $deletedCount = 0;
            $batchSize = 1000;

            TrafficStatistic::where('stat_date', '<', $cutoffDate)
                ->chunkById($batchSize, function ($records) use (&$deletedCount) {
                    $ids = $records->pluck('id')->toArray();
                    $count = TrafficStatistic::whereIn('id', $ids)->delete();
                    $deletedCount += $count;
                    $this->info("已删除 {$deletedCount} 条记录...");
                });

            $this->info("清理完成！共删除 {$deletedCount} 条记录。");

            Log::info('流量统计数据清理完成', [
                'days' => $days,
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                'deleted_count' => $deletedCount,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("清理失败: {$e->getMessage()}");
            Log::error('流量统计数据清理失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

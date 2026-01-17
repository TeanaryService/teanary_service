<?php

namespace App\Jobs;

use App\Models\TrafficStatistic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchWriteTrafficStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 执行任务
     */
    public function handle(): void
    {
        try {
            // 获取过去5分钟内所有待写入的队列
            $queues = $this->getPendingQueues();

            if (empty($queues)) {
                Log::debug('流量统计：没有待写入的数据');
                return;
            }

            $totalRecords = 0;
            $batchData = [];

            // 合并所有队列数据
            foreach ($queues as $queueKey => $queue) {
                foreach ($queue as $data) {
                    // 生成唯一键用于去重（包含 is_bot 和 spider_source）
                    $isBot = $data['is_bot'] ?? false;
                    $spiderSource = $data['spider_source'] ?? null;
                    $uniqueKey = md5(
                        $data['stat_date'] . ':' . 
                        $data['path'] . ':' . 
                        $data['method'] . ':' . 
                        md5($data['ip']) . ':' .
                        ($isBot ? '1' : '0') . ':' .
                        ($spiderSource ?? '')
                    );

                    if (isset($batchData[$uniqueKey])) {
                        // 如果已存在，累加计数
                        $batchData[$uniqueKey]['count'] += $data['count'];
                    } else {
                        $batchData[$uniqueKey] = [
                            'stat_date' => Carbon::parse($data['stat_date']),
                            'path' => $data['path'],
                            'method' => $data['method'],
                            'ip' => $data['ip'],
                            'user_agent' => $data['user_agent'] ?? null,
                            'referer' => $data['referer'] ?? null,
                            'locale' => $data['locale'] ?? null,
                            'is_bot' => $isBot,
                            'spider_source' => $spiderSource,
                            'count' => $data['count'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // 删除已处理的队列
                Cache::forget($queueKey);
            }

            if (empty($batchData)) {
                return;
            }

            // 批量插入数据库
            $chunks = array_chunk($batchData, 100);
            foreach ($chunks as $chunk) {
                // 使用 insertOrIgnore 避免重复数据
                // 但由于我们使用雪花ID，需要先检查是否存在
                $this->insertOrUpdateBatch($chunk);
                $totalRecords += count($chunk);
            }

            Log::info('流量统计批量写入完成', [
                'total_records' => $totalRecords,
                'queues_processed' => count($queues),
            ]);
        } catch (\Exception $e) {
            Log::error('流量统计批量写入失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * 获取待写入的队列
     */
    protected function getPendingQueues(): array
    {
        $queues = [];
        $now = now();

        // 获取过去5分钟的所有队列键
        for ($i = 0; $i < 5; $i++) {
            $date = $now->copy()->subMinutes($i);
            $queueKey = 'traffic:queue:' . $date->format('Y-m-d-H-i');
            $queue = Cache::get($queueKey);

            if ($queue && is_array($queue) && !empty($queue)) {
                $queues[$queueKey] = $queue;
            }
        }

        return $queues;
    }

    /**
     * 批量插入或更新
     */
    protected function insertOrUpdateBatch(array $chunk): void
    {
        foreach ($chunk as $data) {
            // 检查是否已存在相同的记录（基于 stat_date, path, method, ip, is_bot, spider_source）
            $query = TrafficStatistic::where('stat_date', $data['stat_date'])
                ->where('path', $data['path'])
                ->where('method', $data['method'])
                ->where('ip', $data['ip'])
                ->where('is_bot', $data['is_bot']);
            
            if ($data['spider_source']) {
                $query->where('spider_source', $data['spider_source']);
            } else {
                $query->whereNull('spider_source');
            }
            
            $existing = $query->first();

            if ($existing) {
                // 更新计数
                $existing->increment('count', $data['count']);
            } else {
                // 创建新记录
                TrafficStatistic::create($data);
            }
        }
    }
}

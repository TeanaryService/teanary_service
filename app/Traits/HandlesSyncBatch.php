<?php

namespace App\Traits;

use App\Models\SyncLog;
use Illuminate\Support\Collection;

trait HandlesSyncBatch
{
    /**
     * 获取待同步记录，确保同一行的多次修改都被包含.
     *
     * 如果同一行有多次修改，确保所有修改都被包含在查询结果中，
     * 避免只查询部分修改导致后续修改覆盖前面的修改.
     */
    protected function getPendingLogsWithGrouping(string $targetNode, int $limit): Collection
    {
        // 先获取所有待同步记录，按创建时间排序
        $allPendingLogs = SyncLog::where('status', 'pending')
            ->where('target_node', $targetNode)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($allPendingLogs->isEmpty()) {
            return collect();
        }

        // 按 model_type 和 model_id 分组
        $grouped = $allPendingLogs->groupBy(function ($log) {
            return "{$log->model_type}:{$log->model_id}";
        });

        // 按每个组的第一个记录的创建时间排序
        $sortedGroups = $grouped->sortBy(function ($group) {
            return $group->first()->created_at;
        });

        // 收集记录，确保同一行的所有修改都被包含
        $pendingLogs = collect();
        foreach ($sortedGroups as $group) {
            // 如果添加这个组的所有记录会超过限制，停止添加
            if ($pendingLogs->count() + $group->count() > $limit) {
                // 如果当前组只有一条记录，可以添加
                if ($group->count() === 1 && $pendingLogs->count() < $limit) {
                    $pendingLogs->push($group->first());
                }
                break;
            }

            // 添加这个组的所有记录（同一行的所有修改）
            $pendingLogs = $pendingLogs->merge($group);
        }

        // 按创建时间排序，确保所有记录都按时间顺序
        return $pendingLogs->sortBy('created_at')->values();
    }

    /**
     * 将同步记录分批，确保同一行的多次修改在同一批次中.
     *
     * 这样可以确保同一行的多次修改按顺序发送，避免被分到不同批次导致顺序错乱.
     */
    protected function chunkLogsByModel(Collection $logs, int $batchSize): Collection
    {
        $chunks = collect();
        $currentChunk = collect();
        $currentModelKey = null;
        $currentModelCount = 0;

        foreach ($logs as $log) {
            $modelKey = "{$log->model_type}:{$log->model_id}";

            // 如果切换到新的模型，或者当前批次已满，开始新批次
            if ($currentModelKey !== $modelKey) {
                // 如果当前批次不为空且已满，保存当前批次
                if ($currentChunk->isNotEmpty() && $currentModelCount >= $batchSize) {
                    $chunks->push($currentChunk);
                    $currentChunk = collect();
                    $currentModelCount = 0;
                }
                $currentModelKey = $modelKey;
            }

            // 如果当前批次已满，开始新批次
            if ($currentModelCount >= $batchSize && $currentChunk->isNotEmpty()) {
                $chunks->push($currentChunk);
                $currentChunk = collect();
                $currentModelCount = 0;
            }

            $currentChunk->push($log);
            ++$currentModelCount;
        }

        // 添加最后一个批次
        if ($currentChunk->isNotEmpty()) {
            $chunks->push($currentChunk);
        }

        return $chunks;
    }
}

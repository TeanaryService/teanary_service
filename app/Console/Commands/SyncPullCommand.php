<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class SyncPullCommand extends Command
{
    protected $signature = 'sync:pull';
    protected $description = '拉取远程数据库中指定表的增量数据，并写入本地';

    public function handle()
    {
        $pullTables = config('sync.pull_from_remote');
        $remoteUrl = rtrim(config('sync.remote_url'), '/');
        $since = Carbon::now()->subMinutes(config('sync.sync_interval_minutes', 10));

        foreach ($pullTables as $table) {
            $this->info("拉取表 [$table] 自 $since");

            $response = Http::post("$remoteUrl/api/sync/pull", [
                'table' => $table,
                'since' => $since->toDateTimeString(),
            ]);

            if (!$response->successful()) {
                $this->error("拉取失败：{$response->body()}");
                continue;
            }

            $records = $response->json('data', []);
            $count = 0;
            
            foreach ($records as $record) {
                if (!isset($record['id'])) {
                    continue;
                }

                DB::table($table)->updateOrInsert(
                    ['id' => $record['id']],
                    $record
                );
                $count++;
            }

            $this->info("更新或插入 $count 条记录");
        }

        $this->info('刷新缓存/索引');
        Artisan::call('scout:refresh-all');
    }
}

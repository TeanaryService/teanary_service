<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class RefreshScoutIndexes extends Command
{
    protected $signature = 'scout:refresh-all';

    protected $description = 'Flush and re-import all Scout indexes based on config';

    public function handle(): void
    {
        $models = collect(config('scout.meilisearch.index-settings'))->keys();

        if ($models->isEmpty()) {
            $this->warn('No models found in config(scout.meilisearch.index-settings).');

            return;
        }

        foreach ($models as $model) {
            $this->info("Flushing index for: {$model}");
            Artisan::call('scout:flush', ['model' => $model]);
            $this->line(Artisan::output());

            $this->info("Importing index for: {$model}");
            Artisan::call('scout:import', ['model' => $model]);
            $this->line(Artisan::output());
        }

        $this->info('All Scout indexes have been refreshed.');

        Cache::flush();

        $this->info('刷新全部缓存');
    }
}

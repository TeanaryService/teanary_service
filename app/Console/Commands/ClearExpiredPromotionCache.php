<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion;
use App\Services\PromotionService;

class ClearExpiredPromotionCache extends Command
{
    protected $signature = 'app:clear-cache-if-expired';
    protected $description = '检查促销活动是否有过期，自动清理促销缓存';

    public function handle()
    {
        $now = now();
        $expired = Promotion::where('ends_at', '<', $now)->exists();
        if ($expired) {
            PromotionService::clearPromotionCache();
            $this->info('促销缓存已清理');
        } else {
            $this->info('无过期促销，无需清理');
        }
    }
}

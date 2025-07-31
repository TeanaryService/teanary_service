<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ClearEmptyCarts extends Command
{
    protected $signature = 'carts:clear-empty';
    protected $description = '清理没有商品的购物车';

    public function handle()
    {
        // 清理空购物车
        $emptyCartsCount = Cart::empty()
            ->where('created_at', '<', Carbon::now()->subDay())
            ->delete();
        
        // 清理匿名但有商品的购物车
        $anonymousCartsCount = Cart::whereNull('user_id')
            ->whereHas('cartItems')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->delete();
            
        $this->info("已清理 {$emptyCartsCount} 个空购物车");
        $this->info("已清理 {$anonymousCartsCount} 个匿名购物车");
        
        return 0;
    }
}

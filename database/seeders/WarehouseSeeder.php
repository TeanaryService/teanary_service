<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::first();
        if (! $warehouse) {
            $warehouse = Warehouse::create([
                'name' => '默认仓库',
                'code' => 'WH-DEFAULT',
                'active' => true,
                'is_default' => true,
                'sort_order' => 0,
            ]);
            // 将现有商品全部关联到默认仓库，避免前台无商品
            $productIds = Product::pluck('id')->toArray();
            foreach ($productIds as $pid) {
                $warehouse->products()->attach($pid);
            }
        }
    }
}

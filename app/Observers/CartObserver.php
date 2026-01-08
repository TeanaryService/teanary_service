<?php

namespace App\Observers;

use App\Models\Cart;

class CartObserver
{
    /**
     * Handle the Cart "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Cart $cart): void
    {
        // 删除购物车项
        $cart->cartItems()->each(function ($item) {
            $item->delete();
        });
    }
}

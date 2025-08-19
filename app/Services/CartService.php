<?php

namespace App\Services;

use App\Models\Cart;

class CartService
{
    /**
     * 只查找，不创建
     */
    public function getCart()
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())->first();
        } else {
            $sessionId = session()->getId();
            return Cart::where('session_id', $sessionId)->first();
        }
    }

    /**
     * 查找或创建
     */
    public function getOrCreateCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        } else {
            $sessionId = session()->getId();
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }
}

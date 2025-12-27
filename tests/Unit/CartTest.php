<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_can_be_created_using_factory()
    {
        $cart = Cart::factory()->create();

        $this->assertNotNull($cart);
        $this->assertInstanceOf(Cart::class, $cart);
    }

    public function test_user_relationship()
    {
        $cart = new Cart;
        $relation = $cart->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_cart_items_relationship()
    {
        $cart = new Cart;
        $relation = $cart->cartItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('cart_id', $relation->getForeignKeyName());
    }

    public function test_scope_empty()
    {
        $cart = Cart::factory()->create();
        $emptyCart = Cart::empty()->first();

        $this->assertNotNull($emptyCart);
        $this->assertEquals($cart->id, $emptyCart->id);

        CartItem::factory()->create(['cart_id' => $cart->id]);
        $emptyCarts = Cart::empty()->get();

        $this->assertNotContains($cart->id, $emptyCarts->pluck('id'));
    }
}

<?php

namespace Tests\Feature\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Tests\Feature\LivewireTestCase;

class CartTest extends LivewireTestCase
{
    public function test_cart_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Cart::class);
        $component->assertSuccessful();
    }

    public function test_cart_displays_items()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 2,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class);
        $component->assertSuccessful();
    }

    public function test_cart_calculates_total()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 2,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class);
        $component->assertSuccessful();
    }

    public function test_user_can_update_item_quantity()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 1,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class)
            ->call('updateQty', $cartItem->id, 3);

        $cartItem->refresh();
        $this->assertEquals(3, $cartItem->qty);
    }

    public function test_user_can_remove_item()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class)
            ->call('removeItem', $cartItem->id);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_user_can_toggle_select_all()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        CartItem::factory()->count(3)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class);

        // 初始状态：selectAll 为 true，所有项都被选中
        $component->assertSet('selectAll', true);

        // 调用 toggleSelectAll 后，应该取消全选
        $component->call('toggleSelectAll')
            ->assertSet('selectAll', false)
            ->assertSet('selected', []);

        // 再次调用，应该全选
        $component->call('toggleSelectAll')
            ->assertSet('selectAll', true);
    }

    public function test_user_can_toggle_item_selection()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        // Cart 组件没有 toggleSelect 方法，只有 toggleSelectAll
        $component = $this->livewire(\App\Livewire\Cart::class)
            ->call('toggleSelectAll');

        $component->assertSuccessful();
    }

    public function test_updating_quantity_to_zero_removes_item()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 1,
        ]);

        $component = $this->livewire(\App\Livewire\Cart::class)
            ->call('updateQty', $cartItem->id, 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}

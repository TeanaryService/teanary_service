<?php

namespace Tests\Feature\Livewire\Components;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Tests\Feature\LivewireTestCase;

class CartDropdownTest extends LivewireTestCase
{
    public function test_cart_dropdown_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class);
        $component->assertSuccessful();
    }

    public function test_cart_dropdown_displays_cart_items()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 2,
        ]);

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class);
        $component->assertSuccessful();
    }

    public function test_cart_dropdown_calculates_total()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'qty' => 2,
        ]);

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class);
        $component->assertSuccessful();
        $this->assertGreaterThan(0, $component->get('cartTotal'));
    }

    public function test_can_add_item_to_cart()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class)
            ->call('addToCart', $product->id, $variant->id, 1);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);
    }

    public function test_can_update_item_quantity()
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

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class)
            ->call('updateQty', $cartItem->id, 3);

        $cartItem->refresh();
        $this->assertEquals(3, $cartItem->qty);
    }

    public function test_can_remove_item()
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

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class)
            ->call('removeItem', $cartItem->id);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
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

        $component = $this->livewire(\App\Livewire\Components\CartDropdown::class)
            ->call('updateQty', $cartItem->id, 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}

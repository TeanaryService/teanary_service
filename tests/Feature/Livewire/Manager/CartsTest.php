<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\Carts;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Tests\Feature\LivewireTestCase;

class CartsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_carts_page_can_be_rendered()
    {
        $component = $this->livewire(Carts::class);
        $component->assertSuccessful();
    }

    public function test_carts_list_displays_carts()
    {
        $cart = Cart::factory()->create();

        $component = $this->livewire(Carts::class);

        $carts = $component->get('carts');
        $cartIds = $carts->pluck('id')->toArray();
        $this->assertContains($cart->id, $cartIds);
    }

    public function test_can_search_carts_by_user_id()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
        $cart2 = Cart::factory()->create(['user_id' => $user2->id]);

        $component = $this->livewire(Carts::class)
            ->set('search', (string) $user1->id);

        $carts = $component->get('carts');
        $cartIds = $carts->pluck('id')->toArray();
        $this->assertContains($cart1->id, $cartIds);
        $this->assertNotContains($cart2->id, $cartIds);
    }

    public function test_can_search_carts_by_user_name()
    {
        $user1 = $this->createUser(['name' => 'John Doe']);
        $user2 = $this->createUser(['name' => 'Jane Smith']);
        $cart1 = Cart::factory()->create(['user_id' => $user1->id]);
        $cart2 = Cart::factory()->create(['user_id' => $user2->id]);

        $component = $this->livewire(Carts::class)
            ->set('search', 'John');

        $carts = $component->get('carts');
        $cartIds = $carts->pluck('id')->toArray();
        $this->assertContains($cart1->id, $cartIds);
        $this->assertNotContains($cart2->id, $cartIds);
    }

    public function test_can_search_carts_by_product_name()
    {
        $cart1 = Cart::factory()->create();
        $cart2 = Cart::factory()->create();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();
        $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product1->id,
            'language_id' => $language->id,
            'name' => '测试商品1',
        ]);
        ProductTranslation::factory()->create([
            'product_id' => $product2->id,
            'language_id' => $language->id,
            'name' => '其他商品',
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart1->id,
            'product_id' => $product1->id,
            'product_variant_id' => $variant1->id,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart2->id,
            'product_id' => $product2->id,
            'product_variant_id' => $variant2->id,
        ]);

        $component = $this->livewire(Carts::class)
            ->set('search', '测试');

        $carts = $component->get('carts');
        $cartIds = $carts->pluck('id')->toArray();
        $this->assertContains($cart1->id, $cartIds);
        $this->assertNotContains($cart2->id, $cartIds);
    }

    public function test_can_filter_carts_by_has_items()
    {
        $cartWithItems = Cart::factory()->create();
        $cartWithoutItems = Cart::factory()->create();

        $product = $this->createProduct();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        CartItem::factory()->create([
            'cart_id' => $cartWithItems->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        $component = $this->livewire(Carts::class)
            ->set('filterHasItems', '1');

        $carts = $component->get('carts');
        $cartIds = $carts->pluck('id')->toArray();
        $this->assertContains($cartWithItems->id, $cartIds);
        $this->assertNotContains($cartWithoutItems->id, $cartIds);
    }

    public function test_can_delete_cart()
    {
        $cart = Cart::factory()->create();

        $component = $this->livewire(Carts::class)
            ->call('deleteCart', $cart->id);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Carts::class)
            ->set('search', 'test')
            ->set('filterHasItems', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterHasItems', '');
    }
}

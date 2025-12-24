<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CartService;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService();
    }

    public function test_get_cart_returns_cart_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);
        
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
        $this->assertEquals($user->id, $result->user_id);
    }

    public function test_get_cart_returns_null_when_no_cart_for_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->service->getCart();

        $this->assertNull($result);
    }

    public function test_get_cart_returns_cart_for_session_when_not_authenticated(): void
    {
        Auth::logout();
        $sessionId = session()->getId();
        $cart = Cart::factory()->create(['session_id' => $sessionId]);

        $result = $this->service->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
        $this->assertEquals($sessionId, $result->session_id);
    }

    public function test_get_or_create_cart_creates_cart_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->service->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }

    public function test_get_or_create_cart_returns_existing_cart_for_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);
        $existingCart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getOrCreateCart();

        $this->assertEquals($existingCart->id, $result->id);
        $this->assertEquals(1, Cart::where('user_id', $user->id)->count());
    }

    public function test_get_or_create_cart_creates_cart_for_session(): void
    {
        Auth::logout();
        $sessionId = session()->getId();

        $result = $this->service->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertEquals($sessionId, $result->session_id);
        $this->assertDatabaseHas('carts', ['session_id' => $sessionId]);
    }
}


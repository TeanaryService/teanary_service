<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService;
    }

    public function test_get_cart_for_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
    }

    public function test_get_cart_for_guest()
    {
        Auth::logout();
        $sessionId = Session::getId();
        $cart = Cart::factory()->create(['session_id' => $sessionId]);

        $result = $this->service->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
    }

    public function test_get_cart_returns_null_when_no_cart_exists()
    {
        Auth::logout();

        $result = $this->service->getCart();

        $this->assertNull($result);
    }

    public function test_get_or_create_cart_for_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->service->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertInstanceOf(Cart::class, $result);
        $this->assertEquals($user->id, $result->user_id);
    }

    public function test_get_or_create_cart_for_guest()
    {
        Auth::logout();
        $sessionId = Session::getId();

        $result = $this->service->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertInstanceOf(Cart::class, $result);
        $this->assertEquals($sessionId, $result->session_id);
    }

    public function test_get_or_create_cart_returns_existing_cart()
    {
        $user = User::factory()->create();
        Auth::login($user);
        $existingCart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getOrCreateCart();

        $this->assertEquals($existingCart->id, $result->id);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    #[Test]
    public function it_returns_null_when_no_cart_exists_for_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->cartService->getCart();

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_cart_for_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->cartService->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
        $this->assertEquals($user->id, $result->user_id);
    }

    #[Test]
    public function it_returns_null_when_no_cart_exists_for_guest()
    {
        $result = $this->cartService->getCart();

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_cart_for_guest_by_session_id()
    {
        $sessionId = Session::getId();
        $cart = Cart::factory()->create(['session_id' => $sessionId]);

        $result = $this->cartService->getCart();

        $this->assertNotNull($result);
        $this->assertEquals($cart->id, $result->id);
        $this->assertEquals($sessionId, $result->session_id);
    }

    #[Test]
    public function it_creates_cart_for_authenticated_user_if_not_exists()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $result = $this->cartService->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_returns_existing_cart_for_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $existingCart = Cart::factory()->create(['user_id' => $user->id]);

        $result = $this->cartService->getOrCreateCart();

        $this->assertEquals($existingCart->id, $result->id);
        $this->assertEquals(1, Cart::where('user_id', $user->id)->count());
    }

    #[Test]
    public function it_creates_cart_for_guest_if_not_exists()
    {
        $sessionId = Session::getId();

        $result = $this->cartService->getOrCreateCart();

        $this->assertNotNull($result);
        $this->assertEquals($sessionId, $result->session_id);
        $this->assertDatabaseHas('carts', [
            'session_id' => $sessionId,
        ]);
    }

    #[Test]
    public function it_returns_existing_cart_for_guest()
    {
        $sessionId = Session::getId();
        $existingCart = Cart::factory()->create(['session_id' => $sessionId]);

        $result = $this->cartService->getOrCreateCart();

        $this->assertEquals($existingCart->id, $result->id);
        $this->assertEquals(1, Cart::where('session_id', $sessionId)->count());
    }
}


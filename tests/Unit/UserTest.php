<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_using_factory()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
    }

    public function test_user_group_relationship()
    {
        $user = new User;
        $relation = $user->userGroup();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_group_id', $relation->getForeignKeyName());
    }

    public function test_addresses_relationship()
    {
        $user = new User;
        $relation = $user->addresses();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_carts_relationship()
    {
        $user = new User;
        $relation = $user->carts();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_orders_relationship()
    {
        $user = new User;
        $relation = $user->orders();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_articles_relationship()
    {
        $user = new User;
        $relation = $user->articles();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_product_reviews_relationship()
    {
        $user = new User;
        $relation = $user->productReviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }
}

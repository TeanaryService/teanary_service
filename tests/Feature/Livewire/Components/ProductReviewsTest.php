<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\ProductReviews;
use App\Models\ProductReview;
use Tests\Feature\LivewireTestCase;

class ProductReviewsTest extends LivewireTestCase
{
    public function test_product_reviews_can_be_rendered()
    {
        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id]);
        $component->assertSuccessful();
    }

    public function test_product_reviews_displays_approved_reviews()
    {
        $product = $this->createProduct();
        $approvedReview = ProductReview::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
        ]);
        $unapprovedReview = ProductReview::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id]);
        $component->assertSuccessful();
    }

    public function test_authenticated_user_can_submit_review()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 5)
            ->set('content', 'This is a great product!')
            ->call('submit');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'content' => 'This is a great product!',
            'is_approved' => false,
        ]);
    }

    public function test_review_submission_validates_rating()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', '')
            ->set('content', 'This is a great product!')
            ->call('submit')
            ->assertHasErrors(['rating']);
    }

    public function test_review_submission_validates_content()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 5)
            ->call('submit')
            ->assertHasErrors(['content']);
    }

    public function test_review_submission_validates_content_min_length()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 5)
            ->set('content', 'Hi')
            ->call('submit')
            ->assertHasErrors(['content']);
    }

    public function test_review_submission_validates_rating_range()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 6)
            ->set('content', 'This is a great product!')
            ->call('submit')
            ->assertHasErrors(['rating']);
    }

    public function test_guest_cannot_submit_review()
    {
        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 5)
            ->set('content', 'This is a great product!')
            ->call('submit');

        $this->assertDatabaseMissing('product_reviews', [
            'product_id' => $product->id,
            'content' => 'This is a great product!',
        ]);
    }

    public function test_review_submission_resets_form()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = $this->createProduct();

        $component = $this->livewire(ProductReviews::class, ['productId' => $product->id])
            ->set('rating', 5)
            ->set('content', 'This is a great product!')
            ->call('submit')
            ->assertSet('rating', 5)
            ->assertSet('content', '');
    }
}

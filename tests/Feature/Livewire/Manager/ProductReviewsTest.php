<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\ProductReviews;
use App\Models\ProductReview;
use Tests\Feature\LivewireTestCase;

class ProductReviewsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->product = $this->createProduct();
    }

    public function test_product_reviews_page_can_be_rendered()
    {
        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id]);
        $component->assertSuccessful();
    }

    public function test_product_reviews_list_displays_reviews()
    {
        $review = ProductReview::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id]);

        $reviews = $component->get('reviews');
        $reviewIds = $reviews->pluck('id')->toArray();
        $this->assertContains($review->id, $reviewIds);
    }

    public function test_product_reviews_only_shows_reviews_for_specific_product()
    {
        $product2 = $this->createProduct();
        $review1 = ProductReview::factory()->create(['product_id' => $this->product->id]);
        $review2 = ProductReview::factory()->create(['product_id' => $product2->id]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id]);

        $reviews = $component->get('reviews');
        $reviewIds = $reviews->pluck('id')->toArray();
        $this->assertContains($review1->id, $reviewIds);
        $this->assertNotContains($review2->id, $reviewIds);
    }

    public function test_can_filter_reviews_by_rating()
    {
        $review1 = ProductReview::factory()->create([
            'product_id' => $this->product->id,
            'rating' => 5,
        ]);
        $review2 = ProductReview::factory()->create([
            'product_id' => $this->product->id,
            'rating' => 3,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id])
            ->set('filterRating', [5]);

        $reviews = $component->get('reviews');
        $reviewIds = $reviews->pluck('id')->toArray();
        $this->assertContains($review1->id, $reviewIds);
        $this->assertNotContains($review2->id, $reviewIds);
    }

    public function test_can_filter_reviews_by_approved_status()
    {
        $approvedReview = ProductReview::factory()->create([
            'product_id' => $this->product->id,
            'is_approved' => true,
        ]);
        $unapprovedReview = ProductReview::factory()->create([
            'product_id' => $this->product->id,
            'is_approved' => false,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id])
            ->set('filterApproved', '1');

        $reviews = $component->get('reviews');
        $reviewIds = $reviews->pluck('id')->toArray();
        $this->assertContains($approvedReview->id, $reviewIds);
        $this->assertNotContains($unapprovedReview->id, $reviewIds);
    }

    public function test_can_toggle_approved_status()
    {
        $review = ProductReview::factory()->create([
            'product_id' => $this->product->id,
            'is_approved' => false,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id])
            ->call('toggleApproved', $review->id);

        $review->refresh();
        $this->assertTrue($review->is_approved);
    }

    public function test_can_delete_review()
    {
        $review = ProductReview::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id])
            ->call('deleteReview', $review->id);

        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(ProductReviews::class, ['productId' => $this->product->id])
            ->set('search', 'test')
            ->set('filterRating', [5])
            ->set('filterApproved', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterRating', [])
            ->assertSet('filterApproved', '');
    }
}

<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product;
use App\Models\ProductReview;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviews extends Component
{
    use HasNavigationRedirect;
    use UsesLocaleCurrency;
    use WithPagination;

    public int $productId;
    public string $search = '';
    public array $filterRating = [];
    public string $filterApproved = '';

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRating(): void
    {
        $this->resetPage();
    }

    public function updatingFilterApproved(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterRating = [];
        $this->filterApproved = '';
        $this->resetPage();
    }

    public function deleteReview(int $id): void
    {
        $review = ProductReview::where('product_id', $this->productId)->findOrFail($id);
        $review->delete();
        $this->flashMessage('deleted_successfully');
    }

    public function toggleApproved(int $id): void
    {
        $review = ProductReview::where('product_id', $this->productId)->findOrFail($id);
        $review->is_approved = ! $review->is_approved;
        $review->save();
        $this->dispatch('flash-message', type: 'success', message: $review->is_approved ? __('manager.product_reviews.approved') : __('manager.product_reviews.pending'));
    }

    #[Computed]
    public function reviews()
    {
        $query = ProductReview::query()
            ->where('product_id', $this->productId)
            ->with([
                'product.productTranslations',
                'productVariant.specificationValues.specificationValueTranslations',
                'user',
            ]);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', '%'.$search.'%');
            });
        }

        if (! empty($this->filterRating)) {
            $query->whereIn('rating', $this->filterRating);
        }

        if ($this->filterApproved !== '') {
            $query->where('is_approved', $this->filterApproved === '1');
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    public function render()
    {
        $product = Product::with('productTranslations')->findOrFail($this->productId);

        return view('livewire.manager.product-reviews', [
            'product' => $product,
            'reviews' => $this->reviews,
            'lang' => $this->getCurrentLanguage(),
        ])->layout('components.layouts.manager');
    }
}

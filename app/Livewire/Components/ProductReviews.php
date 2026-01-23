<?php

namespace App\Livewire\Components;

use App\Models\ProductReview;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviews extends Component
{
    use WithPagination;

    public $productId;

    public $rating = 5;

    public $content = '';

    public $variantId = null;

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'content' => 'required|string|min:5|max:1000',
        'variantId' => 'nullable|integer|exists:product_variants,id',
    ];

    public function submit()
    {
        $this->validate();

        if (! auth()->check()) {
            return;
        }

        ProductReview::create([
            'product_id' => $this->productId,
            'product_variants' => $this->variantId,
            'user_id' => auth()->id(),
            'rating' => $this->rating,
            'content' => $this->content,
            'is_approved' => false,
        ]);

        $this->reset('rating', 'content', 'variantId');
        session()->flash('review_submitted', __('app.review_submitted'));
    }

    /**
     * 获取产品变体规格字符串.
     */
    protected function getProductVariantSpecs($productVariant, $lang): string
    {
        if (! $productVariant) {
            return '';
        }

        return $productVariant->specificationValues
            ->map(function ($sv) use ($lang) {
                $trans = $sv->specificationValueTranslations
                    ->where('language_id', $lang?->id)
                    ->first();

                return $trans && $trans->name ? $trans->name : $sv->id;
            })
            ->implode(' / ');
    }

    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
        $reviews = ProductReview::with(['user', 'productVariant.specificationValues.specificationValueTranslations'])
            ->where('product_id', $this->productId)
            ->where('is_approved', true)
            ->latest('id')
            ->paginate(10);

        return view('livewire.components.product-reviews', [
            'reviews' => $reviews,
            'lang' => $lang,
        ]);
    }
}

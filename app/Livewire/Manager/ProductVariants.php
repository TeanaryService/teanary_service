<?php

namespace App\Livewire\Manager;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ProductVariants extends Component
{
    use WithPagination;

    public int $productId;
    public string $search = '';

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteVariant(int $id): void
    {
        $variant = ProductVariant::where('product_id', $this->productId)->findOrFail($id);
        $variant->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function variants()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        $query = ProductVariant::query()
            ->where('product_id', $this->productId)
            ->with([
                'specificationValues.specificationValueTranslations',
                'specificationValues.specification.specificationTranslations',
            ]);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where('sku', 'like', '%' . $search . '%');
        }

        $paginator = $query->orderByDesc('created_at')->paginate(15);

        $paginator->getCollection()->transform(function (ProductVariant $variant) use ($service) {
            $locale = app()->getLocale();
            $lang = $service->getLanguageByCode($locale);
            $items = [];

            foreach ($variant->specificationValues as $sv) {
                $spec = $sv->specification;
                $specName = '';
                if ($spec) {
                    $specTrans = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
                    $specName = $specTrans && $specTrans->name
                        ? $specTrans->name
                        : ($spec->specificationTranslations->first()->name ?? '');
                }
                $valTrans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                $valName = $valTrans && $valTrans->name
                    ? $valTrans->name
                    : ($sv->specificationValueTranslations->first()->name ?? '');
                $items[] = $specName . ':' . $valName;
            }

            $variant->spec_values_text = implode('，', array_filter($items));

            return $variant;
        });

        return $paginator;
    }

    public function render()
    {
        $product = Product::findOrFail($this->productId);

        return view('livewire.manager.product-variants', [
            'product' => $product,
            'variants' => $this->variants,
        ])->layout('components.layouts.manager');
    }
}


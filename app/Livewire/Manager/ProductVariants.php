<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductVariants extends Component
{
    use HasDeleteAction;
    use HasNavigationRedirect;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function deleteVariant(int $id): void
    {
        $variant = ProductVariant::where('product_id', $this->productId)->findOrFail($id);
        $variant->delete();
        $this->flashMessage('deleted_successfully');
    }

    #[Computed]
    public function variants()
    {
        $lang = $this->getCurrentLanguage();
        $service = $this->getLocaleService();

        $query = ProductVariant::query()
            ->where('product_id', $this->productId)
            ->with([
                'specificationValues.specificationValueTranslations',
                'specificationValues.specification.specificationTranslations',
            ]);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where('sku', 'like', '%'.$search.'%');
        }

        $paginator = $query->orderByDesc('created_at')->paginate(15);

        $lang = $this->getCurrentLanguage();
        $paginator->getCollection()->transform(function (ProductVariant $variant) use ($lang) {
            $items = [];

            foreach ($variant->specificationValues as $sv) {
                $spec = $sv->specification;
                $specName = '';
                if ($spec) {
                    $specName = $this->translatedField($spec->specificationTranslations, $lang, 'name', '');
                }
                $valName = $this->translatedField($sv->specificationValueTranslations, $lang, 'name', '');
                $items[] = $specName.':'.$valName;
            }

            $variant->spec_values_text = implode('，', array_filter($items));

            return $variant;
        });

        return $paginator;
    }

    public function render()
    {
        $product = Product::with('productTranslations')->findOrFail($this->productId);
        $lang = $this->getCurrentLanguage();
        $productDisplayName = $this->translatedField($product->productTranslations, $lang, 'name', $product->slug);

        return view('livewire.manager.product-variants', [
            'product' => $product,
            'productDisplayName' => $productDisplayName,
            'variants' => $this->variants,
        ])->layout('components.layouts.manager');
    }
}

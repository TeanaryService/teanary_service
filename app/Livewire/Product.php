<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product as ProductModel;
use App\Models\Category;
use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;

class Product extends Component
{
    private $categoryId;
    private $categories;
    private $products = [];
    private $attributeFilters = [];
    private $allAttributes = [];

    public function mount(Request $request)
    {
        $slug = $request->input('slug');
        $search = $request->input('search');
        $this->attributeFilters = $request->input('attributes', []);

        $langId = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale())?->id;
        $this->categories = \App\Models\Category::getCategoriesForLanguage($langId);
        $this->allAttributes = \App\Models\Attribute::getAttributesForLanguage($langId);

        if ($slug) {
            $category = $this->categories->where('slug', $slug)->first();
            if(!$category){
                abort(404);
            }
            $this->categoryId = $category['id'];
        }

        // 如果提供了category_id但在分类树中找不到，则返回404
        if ($this->categoryId) {
            $categoryExists = collect($this->categories)->contains(function ($category) {
                return $category['id'] == $this->categoryId ||
                    collect($category['children'] ?? [])->contains('id', $this->categoryId);
            });

            if (!$categoryExists) {
                abort(404);
            }
        }

        $query = ProductModel::with(['productTranslations', 'productVariants.media', 'productCategories', 'attributeValues', 'media']);

        // 添加搜索条件
        if ($search) {
            $ids = ProductModel::search($search)->keys();
            $query->whereIn('id', $ids);
        }

        if ($this->categoryId) {
            $query->whereHas('productCategories', function ($q) {
                $q->where('id', $this->categoryId);
            });
        }

        // 属性筛选
        foreach ($this->attributeFilters as $attrId => $valueIds) {
            if (!empty($valueIds)) {
                $query->whereHas('attributeValues', function ($q) use ($valueIds) {
                    $q->whereIn('attribute_value_id', (array)$valueIds);
                });
            }
        }

        $this->products = $query->latest('id')->paginate(16);
    }

    public function render()
    {
        return view('livewire.product', [
            'categories' => $this->categories,
            'attributes' => $this->allAttributes,
            'products' => $this->products,
            'categoryId' => $this->categoryId,
            'attributeFilters' => $this->attributeFilters,
        ]);
    }
}
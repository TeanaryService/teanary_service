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
    private $search;
    private $categories = [];
    private $products = [];

    public function mount(Request $request)
    {
        $this->categoryId = $request->input('category_id');
        $this->search = $request->input('search');

        $this->categories = Category::getCachedCategories();

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

        $query = ProductModel::with(['productTranslations', 'productVariants.media', 'productCategories']);

        if ($this->categoryId) {
            $query->whereHas('productCategories', function ($q) {
                $q->where('id', $this->categoryId);
            });
        }

        if ($this->search) {
            $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
            $query->whereHas('productTranslations', function ($q) use ($lang) {
                $q->where('language_id', $lang?->id)
                  ->where('name', 'like', '%' . $this->search . '%');
            });
        }

        $this->products = $query->latest('id')->paginate(16);
    }

    public function render()
    {
        return view('livewire.product', [
            'categories' => $this->categories,
            'products' => $this->products,
            'categoryId' => $this->categoryId,
            'search' => $this->search,
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Product as ProductModel;
use Illuminate\Http\Request;
use Livewire\Component;

class Product extends Component
{
    use UsesLocaleCurrency;
    private $categoryId;

    private $categories;

    private $products = [];

    private $attributeFilters = [];

    /** @var \Illuminate\Support\Collection */
    private $allAttributes;

    public function mount(Request $request)
    {
        $slug = $request->input('slug');
        $search = $request->input('search');
        $this->attributeFilters = $request->input('attributes', []);

        $langId = $this->getCurrentLanguage()?->id;
        $this->categories = \App\Models\Category::getCategoriesForLanguage($langId);
        $this->allAttributes = \App\Models\Attribute::getAttributesForLanguage($langId);

        if ($slug) {
            $category = $this->categories->where('slug', $slug)->first();
            if (! $category) {
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

            if (! $categoryExists) {
                abort(404);
            }
        }

        $query = ProductModel::with(['productTranslations', 'productVariants.media', 'productCategories', 'attributeValues', 'media'])
            ->active();

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
            if (! empty($valueIds)) {
                $query->whereHas('attributeValues', function ($q) use ($valueIds) {
                    $q->whereIn('attribute_value_id', (array) $valueIds);
                });
            }
        }

        $this->products = $query->latest('id')->paginate(16);
    }

    private function buildSeoData(): array
    {
        $seoTitle = '';
        $seoDesc = '';
        $seoImage = asset('logo.svg');
        $seoKeywords = '';

        if ($this->categoryId && ! empty($this->categories)) {
            $lang = $this->getCurrentLanguage();
            $category = collect($this->categories)
                ->flatMap(function ($cat) {
                    return array_merge([$cat], $cat['children']->toArray() ?? []);
                })
                ->firstWhere('id', $this->categoryId);
            if ($category) {
                $seoTitle = $category['name'];
                $seoDesc = $category['name'];
                $seoImage = $category['image_url'] ?? asset('logo.svg');
            }
        } else {
            $seoTitle = __('home.product_list_seo_title');
            $seoDesc = __('home.product_list_seo_desc');
            $seoImage = asset('logo.svg');
        }

        // 筛选条件加到keywords
        if (! empty($this->attributeFilters) && ! empty($this->allAttributes)) {
            $filterNames = [];
            foreach ($this->attributeFilters as $attrId => $valueIds) {
                $attr = collect($this->allAttributes)->firstWhere('id', $attrId);
                if ($attr && ! empty($valueIds)) {
                    foreach ((array) $valueIds as $vid) {
                        $val = collect($attr['values'])->firstWhere('id', $vid);
                        if ($val) {
                            $filterNames[] = $attr['name'].':'.$val['name'];
                        }
                    }
                }
            }
            if ($filterNames) {
                $strFilterName = implode(',', $filterNames);
                $seoKeywords .= $strFilterName;
                $seoTitle = $strFilterName.$seoTitle;
                $seoDesc .= $strFilterName;
            }
        }

        return [
            'title' => $seoTitle,
            'description' => $seoDesc,
            'image' => $seoImage,
            'keywords' => $seoKeywords,
        ];
    }

    public function render()
    {
        $seoData = $this->buildSeoData();

        // 确保 attributes 是数组格式
        // getAttributesForLanguage 返回的 Collection 中每个元素已经是数组格式
        $attributesArray = [];
        if ($this->allAttributes instanceof \Illuminate\Support\Collection && $this->allAttributes->isNotEmpty()) {
            // 直接转换为数组，因为 Collection 中的每个元素已经是数组
            $attributesArray = $this->allAttributes->toArray();
            
            // 过滤掉没有属性值的属性
            $attributesArray = array_filter($attributesArray, function ($attr) {
                return is_array($attr) 
                    && isset($attr['values']) 
                    && is_array($attr['values']) 
                    && !empty($attr['values']);
            });
        }
        
        return view('livewire.product', [
            'categories' => $this->categories,
            'filterAttributes' => $attributesArray,
            'products' => $this->products,
            'categoryId' => $this->categoryId,
            'attributeFilters' => $this->attributeFilters,
            'seoTitle' => $seoData['title'],
            'seoDesc' => $seoData['description'],
            'seoImage' => $seoData['image'],
            'seoKeywords' => $seoData['keywords'],
        ]);
    }
}

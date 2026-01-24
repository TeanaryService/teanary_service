<?php

namespace App\Livewire;

use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Article;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;

class Search extends Component
{
    use UsesLocaleCurrency;

    #[Url]
    public $query = '';

    public function mount()
    {
        $this->query = request('search', '');
    }

    public function render()
    {
        $products = collect([]);
        $articles = collect([]);
        $langId = $this->getCurrentLanguage()?->id;

        if ($this->query) {
            // 搜索商品
            $productIds = Product::search($this->query)->keys();
            $products = Product::with(['productTranslations', 'productVariants.media', 'media'])
                ->active()
                ->whereIn('id', $productIds)
                ->take(5)
                ->get();

            // 如果没有搜索到商品,则随机显示5个
            if ($products->isEmpty()) {
                $products = Product::with(['productTranslations', 'productVariants.media', 'media'])
                    ->active()
                    ->inRandomOrder()
                    ->take(5)
                    ->get();
            }

            // 搜索文章
            $articleIds = Article::search($this->query)->keys();
            $articles = Article::with(['media', 'articleTranslations' => fn ($q) => $q->where('language_id', $langId)])
                ->whereIn('id', $articleIds)
                ->where('is_published', true)
                ->take(5)
                ->get();
        }

        return view('livewire.search', [
            'products' => $products,
            'articles' => $articles,
        ]);
    }
}

<?php

namespace App\View\Components;

use App\Services\PromotionService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PromotionList extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $promotions = app(PromotionService::class)->getAvailablePromotions(auth()->user());

        return view('components.widgets.promotion-list', [
            'promotions' => $promotions,
        ]);
    }
}

@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('products', __('manager.product_reviews.label'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="products" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ __('manager.product_reviews.label') }} -
                        {{ $product->productTranslations->where('language_id', $lang?->id)->first()?->name ?? $product->productTranslations->first()?->name ?? $product->slug }}
                    </h1>
                </div>


                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="{{ __('manager.product_reviews.content') }}"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.product_reviews.rating') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterRating" 
                                :options="[
                                    ['value' => '5', 'label' => '5 ⭐'],
                                    ['value' => '4', 'label' => '4 ⭐'],
                                    ['value' => '3', 'label' => '3 ⭐'],
                                    ['value' => '2', 'label' => '2 ⭐'],
                                    ['value' => '1', 'label' => '1 ⭐']
                                ]"
                                :multiple="false"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.product_reviews.is_approved') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterApproved" 
                                :options="[
                                    ['value' => '', 'label' => __('app.all')],
                                    ['value' => '1', 'label' => __('manager.product_reviews.approved')],
                                    ['value' => '0', 'label' => __('manager.product_reviews.pending')]
                                ]"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-widgets.button 
                            wire:click="resetFilters"
                            variant="secondary"
                        >
                            {{ __('app.reset') }}
                        </x-widgets.button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.product_reviews.user') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.product_reviews.rating') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.product_reviews.content') }}
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('manager.product_reviews.is_approved') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.created_at') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reviews as $review)
                                    <tr class="hover:bg-gray-50 transition-colors align-top">
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ $review->user?->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $review->rating }} ⭐
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xl">
                                            <p class="whitespace-pre-line">{{ $review->content }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <button 
                                                wire:click="toggleApproved({{ $review->id }})"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $review->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}"
                                            >
                                                {{ $review->is_approved ? __('manager.product_reviews.approved') : __('manager.product_reviews.pending') }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $review->created_at?->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button 
                                                wire:click="deleteReview({{ $review->id }})"
                                                wire:confirm="{{ __('app.confirm_delete') }}"
                                                class="text-red-600 hover:text-red-700"
                                            >
                                                {{ __('app.delete') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-10 h-10 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>{{ __('app.no_data') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $reviews->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.product_reviews.label') }}" />
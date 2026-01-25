@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('promotions', __('app.manage') . ': ' . $promotionName, __('manager.promotions.label'), locaRoute('manager.promotions'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="promotions" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.manage') }}: {{ $promotionName }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ __('manager.promotion.type') }}: {{ $promotion->type->label() }}</p>
                    </div>
                    <x-widgets.button 
                        href="{{ locaRoute('manager.promotions') }}" wire:navigate 
                        variant="secondary"
                        class="inline-flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('app.back') }}
                    </x-widgets.button>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <div class="space-y-6">
                    {{-- 促销规则 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('manager.promotion.rules') }}</h2>
                            <x-widgets.button 
                                type="button"
                                wire:click="addRule"
                                variant="secondary"
                                size="sm"
                            >
                                {{ __('app.add') }}
                            </x-widgets.button>
                        </div>

                        @if(empty($rules))
                            <p class="text-sm text-gray-500 mb-4">{{ __('manager.promotion.no_rules') }}</p>
                        @else
                            <div class="space-y-4 mb-4">
                                @foreach($rules as $index => $rule)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <x-widgets.label>{{ __('manager.promotion.condition_type') }}</x-widgets.label>
                                                <x-widgets.select 
                                                    wire="rules.{{ $index }}.condition_type"
                                                    :options="collect($conditionTypeOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->toArray()"
                                                />
                                            </div>
                                            <div>
                                                <x-widgets.label>{{ __('manager.promotion.condition_value') }}</x-widgets.label>
                                                <x-widgets.input 
                                                    type="number"
                                                    step="0.01"
                                                    wire="rules.{{ $index }}.condition_value"
                                                />
                                            </div>
                                            <div>
                                                <x-widgets.label>{{ __('manager.promotion.discount_type') }}</x-widgets.label>
                                                <x-widgets.select 
                                                    wire="rules.{{ $index }}.discount_type"
                                                    :options="collect($discountTypeOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->toArray()"
                                                />
                                            </div>
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <x-widgets.label>{{ __('manager.promotion.discount_value') }}</x-widgets.label>
                                                    <x-widgets.input 
                                                        type="number"
                                                        step="0.01"
                                                        wire="rules.{{ $index }}.discount_value"
                                                    />
                                                </div>
                                                <x-widgets.button 
                                                    type="button"
                                                    wire:click="removeRule({{ $index }})"
                                                    variant="danger-outline"
                                                    size="sm"
                                                    class="!p-2"
                                                >
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </x-widgets.button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex justify-end">
                            <x-widgets.button 
                                type="button"
                                wire:click="saveRules"
                            >
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>

                    {{-- 用户组 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.promotion.user_groups') }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ __('manager.promotion.user_groups_helper') }}</p>
                        
                        @if(count($selectedUserGroupIds) > 0)
                            <div class="mb-4 p-3  border border-teal-200 rounded-lg">
                                <p class="text-sm font-medium text-teal-800 mb-2">{{ __('manager.promotion.selected_user_groups') }}:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($userGroups as $group)
                                        @if(in_array($group['id'], $selectedUserGroupIds))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
                                                {{ $group['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="space-y-2 mb-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                            @foreach($userGroups as $group)
                                <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <x-widgets.checkbox 
                                        wire="selectedUserGroupIds"
                                        :value="$group['id']"
                                    />
                                    <span class="text-sm font-medium text-gray-900">{{ $group['name'] }}</span>
                                    <span class="text-xs text-gray-500">(ID: {{ $group['id'] }})</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end">
                            <x-widgets.button 
                                type="button"
                                wire:click="saveUserGroups"
                            >
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>

                    {{-- 商品变体 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.promotion.product_variants') }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ __('manager.promotion.product_variants_helper') }}</p>
                        
                        <div class="mb-4">
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text"
                                wire:model.live.debounce.300ms="productSearch"
                                placeholder="{{ __('manager.promotion.search_products_placeholder') }}"
                            />
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto mb-4">
                            <div class="space-y-2">
                                @foreach($availableProductVariants as $variant)
                                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <x-widgets.checkbox 
                                            wire="selectedProductVariantIds"
                                            :value="$variant['id']"
                                        />
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ $variant['product_name'] }}</div>
                                            <div class="text-xs text-gray-500">
                                                SKU: {{ $variant['sku'] }}
                                                @if($variant['specs'])
                                                    | {{ $variant['specs'] }}
                                                @endif
                                                @if($variant['price'])
                                                    | {{ __('app.price') }}: {{ $variant['price'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <x-widgets.button 
                                type="button"
                                wire:click="saveProductVariants"
                            >
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('app.manage') }}: {{ $promotionName }}" />

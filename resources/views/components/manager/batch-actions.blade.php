@props([
    'hasTranslationStatus' => false,
    'hasPublishedStatus' => false,
    'hasActiveStatus' => false,
    'hasProductStatus' => false,
    'translationStatusOptions' => [],
    'statusOptions' => [],
    'deleteMethod' => 'batchDelete',
    'translationStatusMethod' => 'batchSetTranslationStatus',
    'publishedStatusMethod' => 'batchSetPublishedStatus',
    'activeStatusMethod' => 'batchSetActiveStatus',
    'productStatusMethod' => 'batchSetProductStatus',
])

@php
    $hasSelected = $this->hasSelectedItems();
    $selectedCount = $this->getSelectedCount();
@endphp

@if($hasSelected)
    <div class="mb-4 p-4 bg-teal-50 border-2 border-teal-200 rounded-xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-2 duration-200">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-500 text-white text-sm font-semibold shadow-sm">
                {{ $selectedCount }}
            </div>
            <span class="text-sm font-semibold text-teal-900">
                {{ __('manager.batch.selected_count', ['count' => $selectedCount]) }}
            </span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- 批量删除 --}}
            <x-widgets.button 
                wire:click="{{ $deleteMethod }}"
                wire:confirm="{{ __('manager.batch.confirm_delete', ['count' => $selectedCount]) }}"
                variant="danger"
                size="sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ __('manager.batch.batch_delete') }}
            </x-widgets.button>

            {{-- 批量设置翻译状态 --}}
            @if($hasTranslationStatus && !empty($translationStatusOptions))
                <div class="relative" x-data="{ value: '' }">
                    <select 
                        x-on:change="if($event.target.value) { $wire.{{ $translationStatusMethod }}($event.target.value); $event.target.value = ''; }"
                        class="px-4 py-2 bg-white border-2 border-teal-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer min-w-[180px]"
                    >
                        <option value="">{{ __('manager.batch.batch_set_translation_status') }}</option>
                        @foreach($translationStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- 批量设置发布状态 --}}
            @if($hasPublishedStatus)
                <div class="flex gap-2">
                    <x-widgets.button 
                        wire:click="{{ $publishedStatusMethod }}(true)"
                        variant="success"
                        size="sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('manager.batch.published') }}
                    </x-widgets.button>
                    <x-widgets.button 
                        wire:click="{{ $publishedStatusMethod }}(false)"
                        variant="secondary"
                        size="sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('manager.batch.unpublished') }}
                    </x-widgets.button>
                </div>
            @endif

            {{-- 批量设置激活状态 --}}
            @if($hasActiveStatus)
                <div class="flex gap-2">
                    <x-widgets.button 
                        wire:click="{{ $activeStatusMethod }}(true)"
                        variant="success"
                        size="sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('manager.batch.activated') }}
                    </x-widgets.button>
                    <x-widgets.button 
                        wire:click="{{ $activeStatusMethod }}(false)"
                        variant="secondary"
                        size="sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('manager.batch.deactivated') }}
                    </x-widgets.button>
                </div>
            @endif

            {{-- 批量设置商品状态 --}}
            @if($hasProductStatus && !empty($statusOptions))
                <div class="relative" x-data="{ value: '' }">
                    <select 
                        x-on:change="if($event.target.value) { $wire.{{ $productStatusMethod }}($event.target.value); $event.target.value = ''; }"
                        class="px-4 py-2 bg-white border-2 border-teal-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer min-w-[180px]"
                    >
                        <option value="">{{ __('manager.batch.batch_set_status') }}</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>
@endif

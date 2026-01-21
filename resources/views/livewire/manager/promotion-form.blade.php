@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('promotions', $promotionId ? __('app.edit') . ' ' . __('filament.PromotionResource.label') : __('app.create') . ' ' . __('filament.PromotionResource.label'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="promotions" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $promotionId ? __('app.edit') : __('app.create') }}
                        {{ __('filament.PromotionResource.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('filament.promotion.type') }}
                                </label>
                                <select wire:model="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="">{{ __('app.please_select') }}</option>
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('filament.promotion.translation_status') }}
                                </label>
                                <select wire:model="translationStatus" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($translationStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('translationStatus') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('filament.promotion.starts_at') }}
                                </label>
                                <input type="datetime-local" wire:model="startsAt"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @error('startsAt') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('filament.promotion.ends_at') }}
                                </label>
                                <input type="datetime-local" wire:model="endsAt"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                @error('endsAt') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="flex items-center mt-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="active" class="rounded border-gray-300 text-teal-600 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ __('filament.promotion.active') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('filament.promotion.translations') }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($languages as $language)
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('filament.promotion.name') }} ({{ $language->name }})
                                        </label>
                                        <input type="text"
                                               wire:model="translations.{{ $language->id }}.name"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @error('translations.' . $language->id . '.name')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('filament.promotion.description') }} ({{ $language->name }})
                                        </label>
                                        <textarea
                                            wire:model="translations.{{ $language->id }}.description"
                                            rows="3"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                        ></textarea>
                                        @error('translations.' . $language->id . '.description')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors">
                            {{ __('app.save') }}
                        </button>
                        <a href="{{ locaRoute('manager.promotions') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('app.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


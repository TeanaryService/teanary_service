@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('promotions', $promotionId ? __('app.edit') . ' ' . __('manager.promotions.label') : __('app.create') . ' ' . __('manager.promotions.label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="promotions" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $promotionId ? __('app.edit') : __('app.create') }}
                        {{ __('manager.promotions.label') }}
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
                            <x-widgets.form-field :label="__('manager.promotion.type')" error="type">
                                <x-widgets.select 
                                    wire="type" 
                                    :options="[['value' => '', 'label' => __('app.please_select')], ...collect($typeOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->toArray()]"
                                    error="type"
                                />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('manager.promotion.translation_status')" error="translationStatus">
                                <x-widgets.select wire="translationStatus" :options="$translationStatusOptions" error="translationStatus" />
                            </x-widgets.form-field>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-widgets.form-field :label="__('manager.promotion.starts_at')" error="startsAt">
                                <x-widgets.input type="datetime-local" wire="startsAt" error="startsAt" />
                            </x-widgets.form-field>
                            <x-widgets.form-field :label="__('manager.promotion.ends_at')" error="endsAt">
                                <x-widgets.input type="datetime-local" wire="endsAt" error="endsAt" />
                            </x-widgets.form-field>
                            <div class="flex items-center mt-6">
                                <x-widgets.checkbox wire="active" :label="__('manager.promotion.active')" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.promotion.translations') }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($languages as $language)
                                <div class="space-y-3">
                                    <x-widgets.form-field :label="__('manager.promotion.name') . ' (' . $language->name . ')'" :error="'translations.' . $language->id . '.name'">
                                        <x-widgets.input 
                                            type="text"
                                            wire="translations.{{ $language->id }}.name"
                                            :error="'translations.' . $language->id . '.name'"
                                        />
                                    </x-widgets.form-field>
                                    <x-widgets.form-field :label="__('manager.promotion.description') . ' (' . $language->name . ')'" :error="'translations.' . $language->id . '.description'">
                                        <x-widgets.textarea
                                            wire="translations.{{ $language->id }}.description"
                                            rows="3"
                                            :error="'translations.' . $language->id . '.description'"
                                        />
                                    </x-widgets.form-field>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <x-widgets.button type="submit">
                            {{ __('app.save') }}
                        </x-widgets.button>
                        <x-widgets.button 
                            href="{{ locaRoute('manager.promotions') }}" wire:navigate
                            variant="secondary"
                        >
                            {{ __('app.cancel') }}
                        </x-widgets.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $promotionId ? __('app.edit') : __('app.create') }} {{ __('manager.promotions.label') }}" keywords="{{ __('manager.promotions.label') }}" />

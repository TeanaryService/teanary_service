<div>
    <h1 class="text-2xl font-bold mb-4">
        {{ $specificationId ? __('app.edit') : __('app.create') }}
        {{ __('manager.specifications.label') }}
    </h1>


    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-widgets.form-field 
                :label="__('manager.specification.translation_status')"
                error="translationStatus"
            >
                <x-widgets.select 
                    wire="translationStatus"
                    :options="collect($translationStatusOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
                />
            </x-widgets.form-field>
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-semibold">
                {{ __('manager.specification.translations') }}
            </h2>

            @php
                $defaultLanguageId = $languages->firstWhere('default', true)?->id ?? $languages->first()?->id;
            @endphp

            <x-widgets.language-tabs :languages="$languages" :defaultId="$defaultLanguageId">
                @foreach($languages as $language)
                    <div
                        data-teany-langpanel="{{ (int) $language->id }}"
                        class="space-y-3 {{ (int) $language->id === (int) ($defaultLanguageId ?? 0) ? '' : 'hidden' }}"
                    >
                        <x-widgets.form-field 
                            :label="__('manager.specification.name')"
                            error="translations.{{ $language->id }}.name"
                        >
                            <x-widgets.input 
                                wire="translations.{{ $language->id }}.name"
                                error="translations.{{ $language->id }}.name"
                            />
                        </x-widgets.form-field>
                    </div>
                @endforeach
            </x-widgets.language-tabs>
        </div>

        <div class="flex gap-2">
            <x-widgets.button type="submit">
                {{ __('app.save') }}
            </x-widgets.button>
            <x-widgets.button 
                href="{{ locaRoute('manager.specifications') }}" wire:navigate
                variant="secondary"
            >
                {{ __('app.cancel') }}
            </x-widgets.button>
        </div>
    </form>
</div>

<x-seo-meta title="{{ $specificationId ? __('app.edit') : __('app.create') }} {{ __('manager.specifications.label') }}" keywords="{{ __('manager.specifications.label') }}" />

<div>
    <h1 class="text-2xl font-bold mb-4">
        {{ $specificationValueId ? __('app.edit') : __('app.create') }}
        {{ __('manager.specification_values.label') }}
    </h1>


    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-widgets.form-field 
                :label="__('manager.specification_value.specification')"
                error="specificationId"
            >
                <x-widgets.select 
                    wire="specificationId"
                    :options="[['value' => '', 'label' => __('app.please_select')], ...collect($specifications)->map(fn($spec) => ['value' => $spec->id, 'label' => $this->getSpecificationName($spec, $spec->specificationTranslations->first()?->language) ?? $spec->id])->toArray()]"
                />
            </x-widgets.form-field>

            <x-widgets.form-field 
                :label="__('manager.specification_value.translation_status')"
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
                {{ __('manager.specification_value.translations') }}
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
                            :label="__('manager.specification_value.name')"
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
                href="{{ locaRoute('manager.specification-values') }}" wire:navigate
                variant="secondary"
            >
                {{ __('app.cancel') }}
            </x-widgets.button>
        </div>
    </form>
</div>

<x-seo-meta title="{{ $specificationValueId ? __('app.edit') : __('app.create') }} {{ __('manager.specification_values.label') }}" keywords="{{ __('manager.specification_values.label') }}" />

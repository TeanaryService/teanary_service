<div>
    <h1 class="text-2xl font-bold mb-4">
        {{ $specificationId ? __('app.edit') : __('app.create') }}
        {{ __('manager.specifications.label') }}
    </h1>

    <x-widgets.session-message type="success" />

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

            @foreach($languages as $language)
                <x-widgets.form-field 
                    :label="__('manager.specification.name') . ' (' . $language->name . ')'"
                    error="translations.{{ $language->id }}.name"
                >
                    <x-widgets.input 
                        wire="translations.{{ $language->id }}.name"
                        error="translations.{{ $language->id }}.name"
                    />
                </x-widgets.form-field>
            @endforeach
        </div>

        <div class="flex gap-2">
            <x-widgets.button type="submit">
                {{ __('app.save') }}
            </x-widgets.button>
            <x-widgets.button 
                href="{{ locaRoute('manager.specifications') }}"
                variant="secondary"
            >
                {{ __('app.cancel') }}
            </x-widgets.button>
        </div>
    </form>
</div>

<x-seo-meta title="{{ $specificationId ? __('app.edit') : __('app.create') }} {{ __('manager.specifications.label') }}" keywords="{{ __('manager.specifications.label') }}" />

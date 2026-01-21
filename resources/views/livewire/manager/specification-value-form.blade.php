<div>
    <h1 class="text-2xl font-bold mb-4">
        {{ $specificationValueId ? __('app.edit') : __('app.create') }}
        {{ __('manager.specification_values.label') }}
    </h1>

    @if (session()->has('message'))
        <div class="mb-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    {{ __('manager.specification_value.specification') }}
                </label>
                <select wire:model="specificationId" class="border rounded px-3 py-2 w-full">
                    <option value="">{{ __('app.please_select') }}</option>
                    @foreach($specifications as $spec)
                        <option value="{{ $spec->id }}">
                            {{ $this->getSpecificationName($spec, $spec->specificationTranslations->first()?->language) ?? $spec->id }}
                        </option>
                    @endforeach
                </select>
                @error('specificationId') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    {{ __('manager.specification_value.translation_status') }}
                </label>
                <select wire:model="translationStatus" class="border rounded px-3 py-2 w-full">
                    @foreach($translationStatusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('translationStatus') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-semibold">
                {{ __('manager.specification_value.translations') }}
            </h2>

            @foreach($languages as $language)
                <div>
                    <label class="block text-sm font-medium mb-1">
                        {{ __('manager.specification_value.name') }} ({{ $language->name }})
                    </label>
                    <input type="text"
                           wire:model="translations.{{ $language->id }}.name"
                           class="border rounded px-3 py-2 w-full">
                    @error('translations.' . $language->id . '.name')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                {{ __('app.save') }}
            </button>
            <a href="{{ locaRoute('manager.specification-values') }}"
               class="px-4 py-2 border rounded">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
</div>

<x-seo-meta title="{{ $specificationValueId ? __('app.edit') : __('app.create') }} {{ __('manager.specification_values.label') }}" keywords="{{ __('manager.specification_values.label') }}" />

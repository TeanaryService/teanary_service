@props([
    'languages', // Collection<Language>
    'defaultId' => null,
    'class' => '',
])

@php
    $defaultLanguageId = $defaultId
        ?? ($languages->firstWhere('default', true)?->id ?? $languages->first()?->id);

    $wrapperClass = trim('space-y-4 ' . $class);
@endphp

<div
    class="{{ $wrapperClass }}"
    data-teany-langtabs
    data-default-lang="{{ (int) ($defaultLanguageId ?? 0) }}"
>
    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        @foreach($languages as $language)
            <button
                type="button"
                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300"
                data-teany-langtab="{{ (int) $language->id }}"
                data-active-class="bg-teal-600 text-white border-teal-600 hover:bg-teal-700 hover:border-teal-700"
                data-inactive-class="bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300"
            >
                {{ $language->name }}
            </button>
        @endforeach
    </div>

    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>


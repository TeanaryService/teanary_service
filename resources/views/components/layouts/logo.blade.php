@props([
    'showText' => true,
])

<div class="flex items-center justify-start md:justify-center px-0 md:px-3 gap-x-2">
    <img class="w-8 h-8 rounded-lg" src="{{ url('logo.jpg') }}" />
    @if ($showText)
        <div class="hidden md:block">
            <p class="text-sm font-bold">{{ config('app.name') }}</p>
            <p class="text-xs">{{ config('app.sub_title') }}</p>
        </div>
    @endif
</div>

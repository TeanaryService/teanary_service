@props([
    'showText' => true,
])

<div class="flex items-center justify-start md:justify-center px-0 md:px-3 gap-x-2">
    <img class="w-11 h-11 rounded-lg" src="{{ url('logo.png') }}" />
    @if ($showText)
        <div class="hidden md:block">
            <p class="text-xl font-bold">{{ config('app.name') }}</p>
        </div>
    @endif
</div>

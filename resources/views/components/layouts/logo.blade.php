@props([
    'showText' => true,
    'imgClass' => '', // 新增属性，允许传入额外的 CSS 类
])

<div class="flex items-center justify-start md:justify-center gap-x-4">
    <img class="h-auto w-auto rounded-lg object-contain {{ $imgClass }}"
        src="{{ asset('logo.png') }}" alt="Logo" />

    @if ($showText)
        <div class="hidden lg:block">
            <p class="text-xl font-bold mb-1">{{ config('app.name') }}</p>
            <p class="text-md">{{ __('app.sub_name') }}</p>
        </div>
    @endif
</div>

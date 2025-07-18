@props([
    'showText' => true,
    'imgClass' => '', // 新增属性，允许传入额外的 CSS 类
])

<div class="flex items-center justify-start md:justify-center gap-x-4">
    <img class="rounded-lg {{ $imgClass }}" src="{{ asset('logo.png') }}" />
    @if ($showText)
        <div class="hidden md:block">
            <p class="text-xl font-bold">{{ config('app.name') }}</p>
        </div>
    @endif
</div>

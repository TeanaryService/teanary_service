@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null, // slot name for actions
    'class' => '',
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 {{ $class }}">
    <div>
        @if($title)
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                {{ $title }}
            </h2>
        @endif
        @if($subtitle)
            <p class="text-base text-gray-600">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-3 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>

@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null, // slot name for actions
    'breadcrumbs' => null, // breadcrumbs array
    'class' => '',
])

<div class="mb-8 {{ $class }}">
    @if($breadcrumbs)
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
    @endif
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @if($title)
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
            @endif
            @if($subtitle)
                <p class="text-base text-gray-600 mt-2">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center gap-3 flex-shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>

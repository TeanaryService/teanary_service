@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null, // slot name for actions
    'class' => '',
])

<div class="space-y-4 {{ $class }}">
    @if($title || $subtitle || isset($actions))
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                @if($title)
                    <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    
    <div>
        {{ $slot }}
    </div>
</div>

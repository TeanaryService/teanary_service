@props([
    'icon' => 'heroicon-o-inbox',
    'title' => null,
    'description' => null,
    'action' => null, // slot name for action button
    'class' => '',
])

<div class="flex flex-col items-center justify-center py-16 px-4 {{ $class }}">
    <div class="w-16 h-16 md:w-20 md:h-20 mb-6 text-gray-300">
        <x-dynamic-component :component="$icon" class="w-full h-full" />
    </div>
    
    @if($title)
        <h3 class="text-xl md:text-2xl font-semibold text-gray-900 mb-3 text-center">
            {{ $title }}
        </h3>
    @endif
    
    @if($description)
        <p class="text-base text-gray-600 mb-6 text-center max-w-md">
            {{ $description }}
        </p>
    @endif
    
    @if(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
    
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>

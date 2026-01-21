@props([
    'label' => null,
    'labelFor' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'class' => '',
])

<div class="space-y-2 {{ $class }}">
    @if($label)
        <x-widgets.label :for="$labelFor" :required="$required" class="text-base font-semibold text-gray-800">
            {{ $label }}
        </x-widgets.label>
    @endif
    
    <div class="relative">
        {{ $slot }}
    </div>
    
    @if($error)
        @error($error)
            <p class="mt-2 text-sm font-medium text-red-600 flex items-center gap-1.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
            </p>
        @enderror
    @endif
    
    @if($help)
        <p class="mt-1.5 text-xs text-gray-500 leading-relaxed">{{ $help }}</p>
    @endif
</div>

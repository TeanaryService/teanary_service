@props([
    'class' => '',
    'dismissible' => true,
    'duration' => 5000,
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left, inline
])

@php
    $positionClasses = match($position) {
        'top-right' => 'fixed top-20 right-2 md:right-4 z-50',
        'top-left' => 'fixed top-20 left-2 md:left-4 z-50',
        'bottom-right' => 'fixed bottom-4 right-2 md:right-4 z-50',
        'bottom-left' => 'fixed bottom-4 left-2 md:left-4 z-50',
        default => '',
    };
    
    $baseContainerClasses = $position === 'inline' 
        ? 'space-y-3' 
        : 'fixed z-50 max-w-sm md:max-w-md w-[calc(100%-1rem)] md:w-full space-y-3 ' . $positionClasses;
    
    $containerClasses = trim($baseContainerClasses . ' ' . $class);
    
    // 收集所有消息
    $messages = [];
    if (session('success')) {
        $messages[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('error')) {
        $messages[] = ['type' => 'error', 'message' => session('error')];
    }
    if (session('warning')) {
        $messages[] = ['type' => 'warning', 'message' => session('warning')];
    }
    if (session('info')) {
        $messages[] = ['type' => 'info', 'message' => session('info')];
    }
    if (session('message')) {
        $messages[] = ['type' => 'info', 'message' => session('message')];
    }
@endphp

@if(!empty($messages))
    <div class="{{ $containerClasses }}">
        @foreach($messages as $index => $message)
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => { show = false; setTimeout(() => $el.remove(), 300); }, {{ $duration }})"
                x-show="show"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform ease-in duration-200 transition"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-full"
                class="mb-3"
            >
                <x-widgets.alert 
                    type="{{ $message['type'] }}" 
                    :message="$message['message']" 
                    :dismissible="$dismissible" 
                    :duration="null"
                />
            </div>
        @endforeach
    </div>
@endif

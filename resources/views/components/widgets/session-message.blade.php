@props([
    'type' => 'message', // message, error, success, warning, info
    'class' => '',
    'dismissible' => true,
    'duration' => 5000, // Auto-dismiss after 5 seconds
])

@php
    $sessionKey = match($type) {
        'error' => 'error',
        'success' => 'success',
        'warning' => 'warning',
        'info' => 'info',
        default => 'message',
    };
    
    $alertType = match($type) {
        'error' => 'error',
        'success' => 'success',
        'warning' => 'warning',
        'info' => 'info',
        default => 'info',
    };
@endphp

@if(session()->has($sessionKey))
    <div class="mb-6 {{ $class }}">
        <x-widgets.alert 
            :type="$alertType" 
            :message="session($sessionKey)"
            :dismissible="$dismissible"
            :duration="$duration"
        />
    </div>
@endif

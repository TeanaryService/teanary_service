@props([
    'class' => '',
    'dismissible' => true,
    'duration' => 5000,
])

<div class="space-y-3 {{ $class }}">
    @if (session('success'))
        <x-widgets.alert type="success" :message="session('success')" :dismissible="$dismissible" :duration="$duration" />
    @endif

    @if (session('error'))
        <x-widgets.alert type="error" :message="session('error')" :dismissible="$dismissible" :duration="$duration" />
    @endif

    @if (session('warning'))
        <x-widgets.alert type="warning" :message="session('warning')" :dismissible="$dismissible" :duration="$duration" />
    @endif

    @if (session('info'))
        <x-widgets.alert type="info" :message="session('info')" :dismissible="$dismissible" :duration="$duration" />
    @endif

    @if (session('message'))
        <x-widgets.alert type="info" :message="session('message')" :dismissible="$dismissible" :duration="$duration" />
    @endif
</div>

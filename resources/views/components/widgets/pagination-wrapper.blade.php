@props([
    'class' => '',
])

<div class="mt-8 md:mt-10 flex justify-center {{ $class }}">
    {{ $slot }}
</div>

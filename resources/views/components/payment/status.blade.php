{{-- resources/views/components/payment/status.blade.php --}}
@props([
    'type' => 'success', // success | error
    'title' => '',
    'message' => '',
    'icon' => ['heroicon-o-check-circle', 'text-teal-500'],
    'button' => [
        'label' => "{{ __('payment.back_home') }}",
        'url' => "{{ localRoute('home') }}",
        'class' => 'bg-teal-600 hover:bg-teal-700'
    ],
])

@php
    $statusClasses = getPaymentStatusClasses($type);
    $bgColor = $statusClasses['bgColor'];
    $textColor = $statusClasses['textColor'];
@endphp

<div class="min-h-[40vh] flex flex-col items-center justify-center {{ $bgColor }} text-center p-6">
    <main role="main" aria-labelledby="payment-status-title" class="bg-white p-8 rounded-xl shadow-md max-w-md w-full relative">
        
        {{-- 动画图标 --}}
        <div class="animate-pop mb-6">
            <x-dynamic-component :component="$icon[0]" class="w-16 h-16 mx-auto {{ $icon[1] }}" />
        </div>

        {{-- 标题 --}}
        <h1 id="payment-status-title" class="text-2xl md:text-3xl font-bold {{ $textColor }} mb-4">
            {{ $title }}
        </h1>

        {{-- 内容 --}}
        <p class="text-gray-700 text-base md:text-lg mb-6">
            {{ $message }}
        </p>

        {{-- 按钮 --}}
        <a href="{{ $button['url'] }}"
            class="inline-block {{ $button['class'] }} text-white font-medium px-6 py-2 rounded-lg shadow transition">
            {{ $button['label'] }}
        </a>
    </main>
</div>

@pushOnce('styles')
    <style>
        @keyframes pop {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }

        .animate-pop {
            animation: pop 0.5s ease-out forwards;
        }
    </style>
@endPushOnce

{{-- resources/views/components/payment/status.blade.php --}}
@props([
    'type' => 'success', // success | error | warning
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

<div class="min-h-[70vh] {{ $bgColor }} flex flex-col items-center justify-center -mt-16 relative">
    <x-widgets.grid-bg />
    <main role="main" aria-labelledby="payment-status-title" class="max-w-xl px-4 text-center relative">
        
        {{-- 动画图标 --}}
        <div class="animate-pop mb-6">
            <x-dynamic-component :component="$icon[0]" class="w-20 h-20 mx-auto {{ $icon[1] }}" />
        </div>

        {{-- 标题 --}}
        <h1 id="payment-status-title" class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4">
            {{ $title }}
        </h1>

        {{-- 内容 --}}
        <p class="text-gray-600 text-base md:text-lg mb-8">
            {{ $message }}
        </p>

        {{-- 按钮 --}}
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="{{ $button['url'] }}" wire:navigate
                class="px-6 py-3 {{ $button['class'] }} text-white rounded-lg transition duration-200 flex items-center gap-2">
                <x-heroicon-o-home class="w-6 h-6" />
                {{ $button['label'] }}
            </a>
        </div>

        {{-- 装饰图形 --}}
        <div class="mt-12 select-none pointer-events-none opacity-75">
            <div class="relative">
                <div class="absolute -top-16 left-1/2 transform -translate-x-1/2 animate-float">
                    <x-heroicon-o-cube class="w-30 h-30 {{ $icon[1] }} opacity-30" />
                </div>
            </div>
        </div>
    </main>
</div>

@pushOnce('styles')
    <style>
        @keyframes pop {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-pop {
            animation: pop 0.5s ease-out forwards;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
@endPushOnce

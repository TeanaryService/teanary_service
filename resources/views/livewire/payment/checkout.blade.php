<div class="min-h-[60vh] bg-teal-100 flex flex-col items-center justify-center -mt-16 relative">
    <x-widgets.grid-bg />
    <main role="main" class="max-w-xl px-4 text-center relative">
        @if($isProcessing)
            {{-- 处理中状态：显示加载指示器 --}}
            <div class="flex flex-col items-center">
                {{-- 简洁的加载指示器 --}}
                <div class="w-16 h-16 border-4 border-teal-200 border-t-teal-600 rounded-full animate-spin mb-6"></div>
                
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4">{{ __('payment.processing') }}</h2>
                <p class="text-gray-600 text-base md:text-lg mb-8">{{ __('payment.redirecting_to_payment') }}</p>
            </div>
        @elseif($errorMessage)
            {{-- 错误状态：显示错误信息和重试按钮 --}}
            <div class="mb-6">
                <div class="animate-pop mb-6">
                    <x-heroicon-o-exclamation-triangle class="w-20 h-20 mx-auto text-red-500" />
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4">{{ __('payment.error_occurred') }}</h2>
                <p class="text-gray-600 text-base md:text-lg mb-8">{{ $errorMessage }}</p>
            </div>
            <div class="flex justify-center gap-4 flex-wrap">
                <x-widgets.button 
                    wire:click="processPayment" 
                    class="px-6 py-3"
                >
                    <x-heroicon-o-arrow-path class="w-6 h-6" />
                    {{ __('payment.retry') }}
                </x-widgets.button>
                <a 
                    href="{{ route('orders.show', $orderId) }}" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition duration-200 flex items-center gap-2"
                >
                    <x-heroicon-o-arrow-left class="w-6 h-6" />
                    {{ __('payment.back_to_order') }}
                </a>
            </div>
        @endif

        {{-- 装饰图形 --}}
        <div class="mt-12 select-none pointer-events-none opacity-75">
            <div class="relative">
                <div class="absolute -top-16 left-1/2 transform -translate-x-1/2 animate-float">
                    <x-heroicon-o-cube class="w-30 h-30 text-teal-100" />
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

@pushOnce('seo')
    <x-seo-meta :title="__('payment.processing')" />

    {{-- JavaScript 处理支付跳转 --}}
    <script data-navigate-once>
        // 使用 livewire:navigated 替代 DOMContentLoaded，确保在 wire:navigate 时也能执行
        (function() {
            function initPayment() {
            // 页面加载完成后立即开始支付流程
            setTimeout(() => {
                @this.processPayment();
            }, 100);

                // 监听支付跳转事件，立即跳转
                Livewire.on('redirect-to-payment', (event) => {
                    window.location.href = event.url;
                });
            }
            
            // 首次加载时执行
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPayment);
            } else {
                initPayment();
            }
            
            // wire:navigate 导航后执行（但 data-navigate-once 确保只执行一次）
            document.addEventListener('livewire:navigated', initPayment, { once: true });
        })();
    </script>
@endPushOnce
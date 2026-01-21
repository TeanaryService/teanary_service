<div class="max-w-4xl mx-auto px-6 md:px-8 min-h-[40vh] flex items-center justify-center">
    <div class="text-center">
        @if($isProcessing)
            {{-- 处理中状态：显示加载指示器 --}}
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
                <div class="flex flex-col items-center">
                    {{-- 简洁的加载指示器 --}}
                    <div class="w-12 h-12 border-3 border-teal-200 border-t-teal-600 rounded-full animate-spin mb-6"></div>
                    
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('payment.processing') }}</h2>
                    <p class="text-gray-600">{{ __('payment.redirecting_to_payment') }}</p>
                </div>
            </div>
        @elseif($errorMessage)
            {{-- 错误状态：显示错误信息和重试按钮 --}}
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto">
                <div class="mb-6">
                    <svg class="w-12 h-12 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h2 class="text-xl font-semibold text-red-800 mb-2">{{ __('payment.error_occurred') }}</h2>
                    <p class="text-red-600 mb-4">{{ $errorMessage }}</p>
                </div>
                <div class="space-y-3">
                    <button 
                        wire:click="processPayment" 
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        {{ __('payment.retry') }}
                    </button>
                    <a 
                        href="{{ route('orders.show', $orderId) }}" 
                        class="block w-full text-center bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        {{ __('payment.back_to_order') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@pushOnce('seo')
    <x-seo-meta :title="__('payment.processing')" />

    {{-- JavaScript 处理支付跳转 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 页面加载完成后立即开始支付流程
            setTimeout(() => {
                @this.processPayment();
            }, 100);

            // 监听支付跳转事件，立即跳转
            Livewire.on('redirect-to-payment', (event) => {
                window.location.href = event.url;
            });
        });
    </script>
@endPushOnce
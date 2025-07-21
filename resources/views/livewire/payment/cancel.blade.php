<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="bg-white shadow-xl rounded-xl p-8 max-w-lg w-full text-center">
        <div class="flex justify-center mb-6">
            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856C19.07 19 20 18.105 20 17V7c0-1.105-.93-2-2.082-2H6.082C4.93 5 4 5.895 4 7v10c0 1.105.93 2 2.082 2z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-3">支付已取消</h1>
        <p class="text-gray-600 mb-6">您已取消了本次支付。如果您有任何疑问，请随时联系客服。</p>

        <a href="{{ route('filament.personal.pages.orders') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-lg transition">
            查看订单
        </a>
    </div>
</div>

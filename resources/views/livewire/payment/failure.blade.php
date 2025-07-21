<div class="min-h-screen flex flex-col items-center justify-center bg-red-50 text-center p-6">
    <div class="bg-white p-8 rounded-xl shadow-md max-w-md w-full">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>

        <h1 class="text-2xl md:text-3xl font-bold text-red-600 mb-4">支付失败</h1>
        <p class="text-gray-700 text-base md:text-lg mb-6">
            很抱歉，您的支付未能成功处理。您可以稍后再试或联系客服获取帮助。
        </p>

        <a href="{{ route('filament.personal.pages.orders') }}"
            class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-lg shadow transition">
            查看订单
        </a>
    </div>
</div>

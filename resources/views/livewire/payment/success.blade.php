<div class="min-h-screen flex flex-col items-center justify-center bg-green-50 p-6">
    <div class="bg-white p-8 rounded-xl shadow-md max-w-md w-full text-center">
        <svg class="w-16 h-16 text-green-500 mx-auto mb-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>

        <h1 class="text-2xl md:text-3xl font-bold text-green-600 mb-4">支付成功！</h1>
        <p class="text-gray-700 text-base md:text-lg mb-6">
            感谢您的购买。您的订单已成功完成。
        </p>

        <a href="{{ locaRoute('home') }}" 
           class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg shadow transition">
            返回首页
        </a>
    </div>
</div>

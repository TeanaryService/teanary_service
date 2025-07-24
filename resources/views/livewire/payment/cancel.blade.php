<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="bg-white shadow-xl rounded-xl p-8 max-w-lg w-full text-center">
        <div class="flex justify-center mb-6">
            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856C19.07 19 20 18.105 20 17V7c0-1.105-.93-2-2.082-2H6.082C4.93 5 4 5.895 4 7v10c0 1.105.93 2 2.082 2z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-3">{{ __('payment.cancel_title') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('payment.cancel_message') }}</p>

        <a href="{{ locaRoute('user.orders') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-lg transition">
            {{ __('payment.view_order') }}
        </a>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('payment.cancel_title') }}" />
@endPushOnce
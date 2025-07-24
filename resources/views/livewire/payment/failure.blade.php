<div class="min-h-screen flex flex-col items-center justify-center bg-red-50 text-center p-6">
    <div class="bg-white p-8 rounded-xl shadow-md max-w-md w-full">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>

        <h1 class="text-2xl md:text-3xl font-bold text-red-600 mb-4">{{ __('payment.failed_title') }}</h1>
        <p class="text-gray-700 text-base md:text-lg mb-6">
            {{ __('payment.failed_message') }}
        </p>

        <a href="{{ locaRoute('user.orders') }}"
            class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-lg shadow transition">
            {{ __('payment.view_order') }}
        </a>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('payment.failed_title') }}" />
@endPushOnce
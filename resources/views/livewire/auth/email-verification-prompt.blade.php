<div class="max-w-xl mx-auto py-16">
    <h2 class="text-2xl font-bold mb-4">{{ __('email_verification.title') }}</h2>

    <p class="mb-4 text-gray-600">{{ __('email_verification.description') }}</p>

    @if ($resent)
        <div class="text-green-600 mb-4">{{ __('email_verification.resent') }}</div>
    @endif

    <button wire:click="sendVerificationEmail"
        class="bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700 transition-colors">
        {{ __('email_verification.resend_button') }}
    </button>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('email_verification.title') }}" />
@endPushOnce

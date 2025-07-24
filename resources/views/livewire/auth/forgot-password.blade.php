<div class="max-w-7xl mx-auto px-4 py-10 flex justify-between">
    <div></div>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ __('auth.reset_password') }}</h2>

        @if ($success)
            <div class="text-teal-600 mb-4">{{ __('passwords.sent') }}</div>
        @else
            <form wire:submit.prevent="sendResetLink">
                <input type="email" wire:model.defer="email" placeholder="{{ __('auth.email') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
                <button type="submit"
                    class="w-full bg-teal-600 text-white font-medium px-8 py-3 rounded-lg hover:bg-teal-700 transition-colors mb-2">
                    {{ __('auth.send_reset_link') }}
                </button>
            </form>
        @endif

        <button wire:click="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">×</button>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('auth.reset_password') }}" />
@endPushOnce

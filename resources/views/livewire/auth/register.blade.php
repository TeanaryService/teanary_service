<div class="max-w-7xl mx-auto px-6">
    <x-auth-center>
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('app.register') }}</h2>
            <form wire:submit.prevent="register">
                <input type="text" wire:model.defer="name" placeholder="{{ __('app.nickname') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <input type="email" wire:model.defer="email" placeholder="{{ __('app.email') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <input type="password" wire:model.defer="password" placeholder="{{ __('app.password') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />

                <input type="password" wire:model.defer="password_confirmation"
                    placeholder="{{ __('app.password_confirmation') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <button type="submit"
                    class="w-full bg-teal-600 text-white font-medium px-8 py-3 rounded-lg hover:bg-teal-700 transition-colors mb-2">
                    {{ __('app.register') }}
                </button>
            </form>

            <div class="flex justify-between mt-4">
                <a href="{{ locaRoute('auth.login') }}" class="text-teal-600 hover:underline">
                    {{ __('app.already_have_account') }}
                </a>
            </div>
        </div>
    </x-auth-center>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.register') }}" />
@endPushOnce

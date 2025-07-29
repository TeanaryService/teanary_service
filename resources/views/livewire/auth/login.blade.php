<div class="max-w-7xl mx-auto px-6">
    <x-auth-center>
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('app.login') }}</h2>

            <form wire:submit.prevent="login">
                <input type="email" wire:model="email" placeholder="{{ __('app.email') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <input type="password" wire:model="password" placeholder="{{ __('app.password') }}"
                    class="w-full mb-4 px-4 py-3 rounded-lg border focus:ring-teal-600" />
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <!-- 记住我 -->
                <label class="inline-flex items-center mb-4">
                    <input type="checkbox" wire:model="remember" class="form-checkbox h-5 w-5 text-teal-600">
                    <span class="ml-2 text-gray-700">{{ __('auth.remember_me') }}</span>
                </label>

                <button type="submit"
                    class="w-full bg-teal-600 text-white font-medium px-8 py-3 rounded-lg hover:bg-teal-700 transition-colors mb-2">
                    {{ __('app.login') }}
                </button>
            </form>

            <div class="flex justify-between mt-4">
                <a href="{{ locaRoute('auth.register') }}" class="text-teal-600 hover:underline">
                    {{ __('app.register') }}
                </a>
                <a href="{{ locaRoute('auth.forgot-password') }}" class="text-teal-600 hover:underline">
                    {{ __('auth.forgot_password') }}
                </a>
            </div>
        </div>
    </x-auth-center>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.login') }}" />
@endPushOnce

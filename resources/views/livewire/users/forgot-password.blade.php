<div class="min-h-[40vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('auth.forgot_password') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('auth.or') }}
                <a href="{{ locaRoute('auth.login') }}" class="font-medium text-teal-600 hover:text-teal-500">
                    {{ __('app.login') }}
                </a>
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="sendResetLink">
            @if ($status)
                <div class="rounded-md bg-teal-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-teal-800">{{ $status }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="rounded-md shadow-sm">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        wire:model="email"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                        placeholder="{{ __('app.email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    {{ __('passwords.send_reset_link') }}
                </button>
            </div>
        </form>
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('auth.forgot_password') }}" description="{{ __('auth.forgot_password') }}"
        keywords="{{ __('auth.forgot_password') }}" />
@endPushOnce

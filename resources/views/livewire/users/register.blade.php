<div class="min-h-[40vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('app.register') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('auth.already_have_account') }}
                <a href="{{ locaRoute('auth.login') }}" class="font-medium text-teal-600 hover:text-teal-500">
                    {{ __('app.login') }}
                </a>
            </p>
        </div>
        <form class="mt-8 space-y-6" wire:submit="register">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('app.nickname') }}</label>
                    <input id="name" name="name" type="text" autocomplete="name" required
                        wire:model="name"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                        placeholder="{{ __('app.nickname') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
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
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('app.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        wire:model="password"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                        placeholder="{{ __('app.password') }}">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('app.password_confirmation') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                        wire:model="password_confirmation"
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm"
                        placeholder="{{ __('app.password_confirmation') }}">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    {{ __('app.register') }}
                </button>
            </div>
        </form>
    </div>
</div>

<x-seo-meta title="{{ __('app.register') }}" />

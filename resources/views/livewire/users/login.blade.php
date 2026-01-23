<div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <x-widgets.grid-bg />
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-xl shadow-md relative">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('app.login') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('auth.or') }}
                <a href="{{ locaRoute('auth.register') }}" wire:navigate class="font-medium text-teal-600 hover:text-teal-500">
                    {{ __('app.register') }}
                </a>
            </p>
        </div>
        <form class="mt-8" wire:submit="login">
            <x-widgets.form-container spacing="space-y-4">
                <x-widgets.form-field :label="__('app.email')" labelFor="email" error="email">
                    <x-widgets.input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required
                        wire="email"
                        placeholder="{{ __('app.email') }}"
                        error="email"
                        class="px-3 py-2 sm:text-sm"
                    />
                </x-widgets.form-field>
                <x-widgets.form-field :label="__('app.password')" labelFor="password" error="password">
                    <x-widgets.input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required
                        wire="password"
                        placeholder="{{ __('app.password') }}"
                        error="password"
                        class="px-3 py-2 sm:text-sm"
                    />
                    </x-widgets.form-field>

                <div class="flex items-center justify-between">
                <x-widgets.checkbox 
                    id="remember" 
                    name="remember" 
                    wire="remember"
                    :label="__('auth.remember_me')"
                />

                <div class="text-sm">
                    <a href="{{ locaRoute('auth.forgot-password') }}" wire:navigate class="font-medium text-teal-600 hover:text-teal-500">
                        {{ __('auth.forgot_password') }}
                    </a>
                </div>
            </div>

                <div>
                    <x-widgets.button type="submit" class="w-full py-2 px-4">
                        {{ __('app.login') }}
                    </x-widgets.button>
                </div>
            </x-widgets.form-container>
        </form>
    </div>
</div>

<x-seo-meta title="{{ __('app.login') }}" />

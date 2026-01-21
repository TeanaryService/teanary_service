<div class="min-h-[40vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('app.login') }}
            </h2>
        </div>
        <form class="mt-8 space-y-6" wire:submit="login">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <x-widgets.label for="email">{{ __('app.email') }}</x-widgets.label>
                    <x-widgets.input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required
                        wire="email"
                        class="rounded-none rounded-t-md relative block focus:z-10 sm:text-sm"
                        placeholder="{{ __('app.email') }}"
                        error="email"
                    />
                </div>
                <div>
                    <x-widgets.label for="password">{{ __('app.password') }}</x-widgets.label>
                    <x-widgets.input 
                        id="password" 
                        name="password" 
                        type="password" 
                        autocomplete="current-password" 
                        required
                        wire="password"
                        class="rounded-none rounded-b-md relative block focus:z-10 sm:text-sm"
                        placeholder="{{ __('app.password') }}"
                        error="password"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <x-widgets.checkbox 
                        id="remember"
                        name="remember"
                        wire="remember"
                        :label="__('auth.remember_me')"
                        class="!gap-2"
                    />
                </div>
            </div>

            <div>
                <x-widgets.button 
                    type="submit"
                    class="w-full py-2 px-4"
                >
                    {{ __('app.login') }}
                </x-widgets.button>
            </div>
        </form>
    </div>
</div>

<x-seo-meta title="{{ __('app.login') }}" />

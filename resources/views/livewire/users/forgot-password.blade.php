<div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-xl shadow-md">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('auth.forgot_password') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('auth.or') }}
                <a href="{{ locaRoute('auth.login') }}" wire:navigate class="font-medium text-teal-600 hover:text-teal-500">
                    {{ __('app.login') }}
                </a>
            </p>
        </div>
        <form class="mt-8" wire:submit="sendResetLink">
            <x-widgets.form-container>
                @if ($status)
                    <div class="rounded-md bg-teal-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-teal-800">{{ $status }}</p>
                            </div>
                        </div>
                    </div>
                @endif

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

                <div>
                    <x-widgets.button type="submit" class="w-full py-2 px-4">
                        {{ __('passwords.send_reset_link') }}
                    </x-widgets.button>
                </div>
            </x-widgets.form-container>
        </form>
    </div>
</div>

<x-seo-meta title="{{ __('auth.forgot_password') }}" />

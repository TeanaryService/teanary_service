<div class="min-h-[40vh] flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('auth.reset_password') }}
            </h2>
        </div>
        <form class="mt-8 space-y-6" wire:submit="resetPassword">
            <div class="space-y-4">
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
                        autocomplete="new-password" 
                        required
                        wire="password"
                        placeholder="{{ __('app.password') }}"
                        error="password"
                        class="px-3 py-2 sm:text-sm"
                    />
                </x-widgets.form-field>
                <x-widgets.form-field :label="__('app.password_confirmation')" labelFor="password_confirmation">
                    <x-widgets.input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        autocomplete="new-password" 
                        required
                        wire="password_confirmation"
                        placeholder="{{ __('app.password_confirmation') }}"
                        class="px-3 py-2 sm:text-sm"
                    />
                </x-widgets.form-field>
            </div>

            <div>
                <x-widgets.button type="submit" class="w-full py-2 px-4">
                    {{ __('auth.reset_password') }}
                </x-widgets.button>
            </div>
        </form>
    </div>
</div>

<x-seo-meta title="{{ __('auth.reset_password') }}" />

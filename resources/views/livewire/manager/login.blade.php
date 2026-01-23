<div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <x-widgets.grid-bg />
    <div class="max-w-md w-full relative">
        <x-widgets.card>
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    {{ __('app.login') }}
                </h2>
            </div>
            <form wire:submit="login">
                <x-widgets.form-container>
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
                        />
                    </x-widgets.form-field>

                    <div class="flex items-center">
                        <x-widgets.checkbox 
                            id="remember"
                            name="remember"
                            wire="remember"
                            :label="__('auth.remember_me')"
                        />
                    </div>

                    <div>
                        <x-widgets.button 
                            type="submit"
                            size="lg"
                            class="w-full"
                        >
                            {{ __('app.login') }}
                        </x-widgets.button>
                    </div>
                </x-widgets.form-container>
            </form>
        </x-widgets.card>
    </div>
</div>

<x-seo-meta title="{{ __('app.login') }}" />

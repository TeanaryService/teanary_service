<div class="inset-0 flex items-center justify-center text-gray-400">
    <section class="w-full bg-gray-50 py-10">
        <div class="max-w-7xl mx-auto px-6 md:px-8">
            <x-widgets.page-title 
                :title="__('app.contact.title')"
                :subtitle="__('app.contact.description')"
                size="xl"
                class="mb-8"
            />
            <div class="flex flex-col md:flex-row items-start justify-between gap-12">
                <div class="w-full">
                    <x-widgets.session-message type="success" />

                    <form wire:submit.prevent="save" class="space-y-4">
                        <x-widgets.form-field error="name">
                            <x-widgets.input 
                                wire="name" 
                                type="text"
                                placeholder="{{ __('app.contact.form.name_placeholder') }}"
                                error="name"
                                class="px-4 py-3"
                            />
                        </x-widgets.form-field>

                        <x-widgets.form-field error="email">
                            <x-widgets.input 
                                wire="email" 
                                type="email"
                                placeholder="{{ __('app.contact.form.email_placeholder') }}"
                                error="email"
                                class="px-4 py-3"
                            />
                        </x-widgets.form-field>

                        <x-widgets.form-field error="message">
                            <x-widgets.textarea 
                                wire="message" 
                                placeholder="{{ __('app.contact.form.message_placeholder') }}"
                                error="message"
                                class="px-4 py-3"
                                rows="4"
                            />
                        </x-widgets.form-field>

                        <x-widgets.button type="submit" class="w-full md:w-auto px-8 py-4">
                            {{ __('app.contact.form.submit') }}
                        </x-widgets.button>
                    </form>
                </div>

                <div class="hidden md:flex items-center bg-gray-100 w-full rounded-2xl p-8 gap gap-6">
                    <div class="text-center w-full">
                        <svg class="w-24 h-24 mx-auto mb-6 text-teal-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('app.contact.side_title') }}</h3>
                        <p class="text-gray-600">{{ __('app.contact.response_time') }}</p>
                    </div>
                    <div class="w-full text-center">
                        <img src="{{ asset('images/my-wechat.jpg') }}" class="w-32 h-32 mx-auto mb-8">
                        <div class="text-xl">{{ __('app.contact.wechat_label') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<x-seo-meta title="{{ __('app.contact.title') }}" description="{{ __('app.contact.description') }}" keywords="{{ __('app.contact.title') }}" />

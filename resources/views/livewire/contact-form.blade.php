<section class="w-full bg-gray-50 min-h-[calc(100vh-200px)] flex items-center py-8">
    <div class="w-full max-w-screen 2xl:max-w-[80vw] mx-auto px-6 md:px-8 w-full">
        <x-widgets.page-title 
            :title="__('app.contact.title')"
            :subtitle="__('app.contact.description')"
            size="xl"
            class="mb-6 text-center"
        />
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
            {{-- 左侧表单区域 --}}
            <div>
                <x-widgets.session-message type="success" />

                <x-widgets.card padding="p-5">
                    <form wire:submit.prevent="save">
                        <x-widgets.form-container spacing="space-y-4">
                            <x-widgets.form-field error="name">
                                <x-widgets.input 
                                    wire="name" 
                                    type="text"
                                    placeholder="{{ __('app.contact.form.name_placeholder') }}"
                                    error="name"
                                />
                            </x-widgets.form-field>

                            <x-widgets.form-field error="email">
                                <x-widgets.input 
                                    wire="email" 
                                    type="email"
                                    placeholder="{{ __('app.contact.form.email_placeholder') }}"
                                    error="email"
                                />
                            </x-widgets.form-field>

                            <x-widgets.form-field error="message">
                                <x-widgets.textarea 
                                    wire="message" 
                                    placeholder="{{ __('app.contact.form.message_placeholder') }}"
                                    error="message"
                                    rows="5"
                                />
                            </x-widgets.form-field>

                            <div class="pt-1">
                                <x-widgets.button type="submit" size="md" class="w-full">
                                    {{ __('app.contact.form.submit') }}
                                </x-widgets.button>
                            </div>
                        </x-widgets.form-container>
                    </form>
                </x-widgets.card>
            </div>

            {{-- 右侧联系信息区域 --}}
            <div>
                <x-widgets.card padding="p-5" :class="'border-none'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- 邮箱联系 --}}
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-44 h-44 bg-teal-100 rounded-full mb-4">
                                <svg class="w-32 h-32 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-1">{{ __('app.contact.side_title') }}</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">{{ __('app.contact.response_time') }}</p>
                        </div>

                        {{-- 微信联系 --}}
                        <div class="text-center">
                            <img src="{{ asset('images/my-wechat.jpg') }}" 
                                 alt="WeChat QR Code" 
                                 class="w-44 h-44 mx-auto mb-3 rounded-lg shadow-md border-2 border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">{{ __('app.contact.wechat_label') }}</p>
                        </div>
                    </div>
                </x-widgets.card>
            </div>
        </div>
    </div>
</section>

<x-seo-meta title="{{ __('app.contact.title') }}" description="{{ __('app.contact.description') }}" keywords="{{ __('app.contact.title') }}" />

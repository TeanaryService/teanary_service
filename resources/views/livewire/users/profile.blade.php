@php
    $breadcrumbs = buildUserCenterBreadcrumbs('profile', __('app.profile'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-users.sidebar active="profile" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('app.profile') }}</h1>
                </div>

                <x-widgets.session-message type="message" />

                <form wire:submit="save">
                    <x-widgets.form-container>
                    {{-- 基本信息卡片 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('app.profile') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- 头像 --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('app.avatar') }}</label>
                                <x-widgets.file-upload 
                                    id="avatar"
                                    wire="avatar"
                                    accept="image/jpeg,image/png,image/gif"
                                    :preview="$avatarUrl"
                                    previewSize="h-24 w-24 rounded-full"
                                    variant="button"
                                    :label="__('app.upload_avatar')"
                                    error="avatar"
                                    :help="__('app.avatar_upload_hint')"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 姓名 --}}
                                <x-widgets.form-field :label="__('app.nickname')" labelFor="name" error="name">
                                    <x-widgets.input 
                                        type="text" 
                                        id="name" 
                                        wire="name"
                                        error="name"
                                        class="sm:text-sm rounded-md"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮箱（只读） --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.email') }}</label>
                                    <input type="email" id="email" value="{{ $email }}" disabled
                                        class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('app.email_cannot_change') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 修改密码卡片 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                {{ __('auth.change_password') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-500 mb-6">{{ __('app.password_leave_blank') }}</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 当前密码 --}}
                                <x-widgets.form-field :label="__('auth.current_password')" labelFor="current_password" error="current_password">
                                    <x-widgets.input 
                                        type="password" 
                                        id="current_password" 
                                        wire="current_password"
                                        error="current_password"
                                        class="sm:text-sm rounded-md"
                                    />
                                </x-widgets.form-field>

                                {{-- 新密码 --}}
                                <x-widgets.form-field :label="__('auth.new_password')" labelFor="password" error="password">
                                    <x-widgets.input 
                                        type="password" 
                                        id="password" 
                                        wire="password"
                                        error="password"
                                        class="sm:text-sm rounded-md"
                                    />
                                </x-widgets.form-field>

                                {{-- 确认密码 --}}
                                <x-widgets.form-field :label="__('app.password_confirmation')" labelFor="password_confirmation" class="md:col-span-2">
                                    <x-widgets.input 
                                        type="password" 
                                        id="password_confirmation" 
                                        wire="password_confirmation"
                                        class="sm:text-sm rounded-md"
                                    />
                                </x-widgets.form-field>
                            </div>
                        </div>
                    </div>

                    {{-- 提交按钮 --}}
                    <div class="flex justify-end gap-3">
                        <x-widgets.button 
                            href="{{ locaRoute('home') }}"
                            variant="secondary"
                            class="px-6 py-2"
                        >
                            {{ __('app.cancel') }}
                        </x-widgets.button>
                        <x-widgets.button type="submit" class="px-6 py-2">
                            {{ __('app.save') }}
                        </x-widgets.button>
                    </div>
                    </x-widgets.form-container>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('app.profile') }}" />

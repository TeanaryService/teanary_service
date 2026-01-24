@php
    $isEdit = $managerId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('managers', $isEdit ? __('app.edit') : __('app.create'), __('manager.managers.label'), locaRoute('manager.managers'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="managers" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.managers.label') }}
                    </h1>
                </div>


                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.user.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 头像上传 --}}
                                <div class="md:col-span-2">
                                    <x-widgets.file-upload 
                                        wire="avatar"
                                        accept="image/*"
                                        :preview="$avatarUrl"
                                        previewSize="w-32 h-32 rounded-full"
                                        :label="__('manager.manager.avatar')"
                                        error="avatar"
                                    />
                                </div>

                                {{-- 名称 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.manager.name')"
                                    labelFor="name"
                                    required
                                    error="name"
                                >
                                    <x-widgets.input 
                                        type="text" 
                                        id="name"
                                        wire="name"
                                        error="name"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮箱 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.manager.email')"
                                    labelFor="email"
                                    required
                                    error="email"
                                >
                                    <x-widgets.input 
                                        type="email" 
                                        id="email"
                                        wire="email"
                                        error="email"
                                    />
                                </x-widgets.form-field>

                                {{-- 密码 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.manager.password') . (!$isEdit ? '' : ' (留空则不修改)')"
                                    labelFor="password"
                                    :required="!$isEdit"
                                    error="password"
                                >
                                    <x-widgets.input 
                                        type="password" 
                                        id="password"
                                        wire="password"
                                        error="password"
                                    />
                                </x-widgets.form-field>

                                {{-- 确认密码 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.manager.password_confirmation')"
                                    labelFor="passwordConfirmation"
                                    :required="!$isEdit"
                                    error="passwordConfirmation"
                                >
                                    <x-widgets.input 
                                        type="password" 
                                        id="passwordConfirmation"
                                        wire="passwordConfirmation"
                                        error="passwordConfirmation"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮箱验证时间 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.manager.email_verified_at')"
                                    labelFor="emailVerifiedAt"
                                    error="emailVerifiedAt"
                                >
                                    <x-widgets.input 
                                        type="datetime-local" 
                                        id="emailVerifiedAt"
                                        wire="emailVerifiedAt"
                                        error="emailVerifiedAt"
                                    />
                                </x-widgets.form-field>

                                {{-- API Token --}}
                                @if($isEdit)
                                    <div>
                                        <label for="token" class="block text-sm font-medium text-gray-700 mb-2">
                                            API Token
                                        </label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="text" 
                                                id="token"
                                                wire:model="token"
                                                readonly
                                                class="flex-1 rounded-lg border-gray-300 shadow-sm bg-gray-50 @error('token') border-red-300 @enderror"
                                                placeholder="点击生成Token按钮生成"
                                            />
                                            <x-widgets.button 
                                                type="button"
                                                wire:click="generateToken"
                                                wire:confirm="确定要生成新的API Token吗？旧的Token将失效。"
                                                class="text-sm"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </x-widgets.button>
                                        </div>
                                        @error('token')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.managers') }}" wire:navigate 
                                variant="secondary"
                            >
                                {{ __('app.cancel') }}
                            </x-widgets.button>
                            <x-widgets.button type="submit">
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.managers.label') }}" keywords="{{ __('manager.managers.label') }}" />

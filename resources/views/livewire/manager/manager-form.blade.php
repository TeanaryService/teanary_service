@php
    $isEdit = $managerId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('managers', $isEdit ? __('app.edit') : __('app.create'), __('manager.managers.label'), locaRoute('manager.managers'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="managers" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.managers.label') }}
                    </h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                <form wire:submit="save" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="space-y-6">
                        {{-- 基本信息 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.user.basic_info') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 头像上传 --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.avatar') }} <span class="text-red-500">*</span>
                                    </label>
                                    @if($avatarUrl)
                                        <div class="mb-4">
                                            <img src="{{ $avatarUrl }}" alt="Current avatar" class="w-32 h-32 rounded-full object-cover border border-gray-300">
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        wire:model="avatar"
                                        accept="image/*"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('avatar') border-red-300 @enderror"
                                    />
                                    @error('avatar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if($avatar)
                                        <p class="mt-1 text-xs text-gray-500">已选择: {{ $avatar->getClientOriginalName() }}</p>
                                    @endif
                                </div>

                                {{-- 名称 --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="name"
                                        wire:model="name"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('name') border-red-300 @enderror"
                                    />
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 邮箱 --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.email') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email"
                                        wire:model="email"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('email') border-red-300 @enderror"
                                    />
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 密码 --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.password') }}
                                        @if(!$isEdit)
                                            <span class="text-red-500">*</span>
                                        @else
                                            <span class="text-xs text-gray-500">(留空则不修改)</span>
                                        @endif
                                    </label>
                                    <input 
                                        type="password" 
                                        id="password"
                                        wire:model="password"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('password') border-red-300 @enderror"
                                    />
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 确认密码 --}}
                                <div>
                                    <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.password_confirmation') }}
                                        @if(!$isEdit)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <input 
                                        type="password" 
                                        id="passwordConfirmation"
                                        wire:model="passwordConfirmation"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('passwordConfirmation') border-red-300 @enderror"
                                    />
                                    @error('passwordConfirmation')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 邮箱验证时间 --}}
                                <div>
                                    <label for="emailVerifiedAt" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('manager.manager.email_verified_at') }}
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        id="emailVerifiedAt"
                                        wire:model="emailVerifiedAt"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('emailVerifiedAt') border-red-300 @enderror"
                                    />
                                    @error('emailVerifiedAt')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

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
                                            <button 
                                                type="button"
                                                wire:click="generateToken"
                                                wire:confirm="确定要生成新的API Token吗？旧的Token将失效。"
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </button>
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
                            <a href="{{ locaRoute('manager.managers') }}" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                {{ __('app.cancel') }}
                            </a>
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors"
                            >
                                {{ __('app.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.managers.label') }}" keywords="{{ __('manager.managers.label') }}" />

@php
    $breadcrumbs = buildUserCenterBreadcrumbs('profile', __('app.profile'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-users.sidebar active="profile" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('app.profile') }}</h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-6">
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
                                <div class="flex items-start gap-6">
                                    <div class="flex-shrink-0">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-24 w-24 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                                        @else
                                            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border-2 border-gray-200 shadow-sm">
                                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <label for="avatar" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            {{ __('app.upload_avatar') }}
                                        </label>
                                        <input type="file" id="avatar" wire:model="avatar" accept="image/jpeg,image/png,image/gif" class="hidden">
                                        <p class="mt-2 text-xs text-gray-500">{{ __('app.avatar_upload_hint') }}</p>
                                        @error('avatar')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 姓名 --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.nickname') }}</label>
                                    <input type="text" id="name" wire:model="name"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

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
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.current_password') }}</label>
                                    <input type="password" id="current_password" wire:model="current_password"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 新密码 --}}
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('auth.new_password') }}</label>
                                    <input type="password" id="password" wire:model="password"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- 确认密码 --}}
                                <div class="md:col-span-2">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.password_confirmation') }}</label>
                                    <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 提交按钮 --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ locaRoute('home') }}"
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('app.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-lg hover:bg-teal-700 transition-colors">
                            {{ __('app.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

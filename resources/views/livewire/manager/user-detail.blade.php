@php
    $user = $this->user;
    $userGroups = $this->userGroups;
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.users.label') }}: {{ $user->name }}</h1>
                </div>
                <a href="{{ locaRoute('manager.users') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    {{ __('app.back') }}
                </a>
            </div>

            @if(session('message'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('message') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- 左侧：编辑表单 --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- 基本信息 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('manager.user.basic_info') }}
                        </h3>
                        <form wire:submit="save" class="space-y-4">
                            {{-- 头像 --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.avatar') }}</label>
                                <div class="flex items-center gap-4">
                                    @if($user->getFirstMediaUrl('avatars'))
                                        <img src="{{ $user->getFirstMediaUrl('avatars') }}" alt="{{ $user->name }}" 
                                            class="w-24 h-24 rounded-full object-cover border-2 border-gray-200">
                                    @else
                                        <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center border-2 border-gray-200">
                                            <span class="text-teal-600 font-semibold text-2xl">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <input type="file" wire:model="avatar" accept="image/*"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                        <p class="mt-1 text-xs text-gray-500">{{ __('app.avatar_upload_hint') }}</p>
                                        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.email') }} <span class="text-red-500">*</span></label>
                                    <input type="email" wire:model="email" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.user_group') }}</label>
                                    <select wire:model="userGroupId" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">{{ __('app.not_available') }}</option>
                                        @foreach($userGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->display_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('userGroupId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.email_verified_at') }}</label>
                                    <input type="datetime-local" wire:model="emailVerifiedAt"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    @error('emailVerifiedAt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('manager.user.password') }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.password') }}</label>
                                        <input type="password" wire:model="password"
                                            placeholder="{{ __('app.password_leave_blank') }}"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('manager.user.password_confirmation') }}</label>
                                        <input type="password" wire:model="passwordConfirmation"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                        @error('passwordConfirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-200">
                                <button type="submit" 
                                    class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                                    {{ __('app.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 右侧：统计信息 --}}
                <div class="space-y-6">
                    {{-- 用户统计 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.user.orders_count') }}</h3>
                        <div class="text-3xl font-bold text-teal-600">{{ $user->orders->count() }}</div>
                    </div>

                    {{-- 最近订单 --}}
                    @if($user->orders->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('orders.recent_orders') }}</h3>
                            <div class="space-y-3">
                                @foreach($user->orders->take(5) as $order)
                                    <div class="border-l-4 border-teal-500 pl-4">
                                        <div class="flex items-center justify-between">
                                            <a href="{{ locaRoute('manager.orders.show', ['order' => $order->id]) }}" 
                                               class="text-sm font-medium text-gray-900 hover:text-teal-600">
                                                {{ $order->order_no }}
                                            </a>
                                            <span class="text-xs text-gray-500">{{ $order->created_at->format('Y-m-d') }}</span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ ($order->currency?->symbol ?? '') . number_format($order->total, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-manager.layout>
</div>

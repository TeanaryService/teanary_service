@php
    $users = $this->users;
    $userGroups = $this->userGroups;
    $locale = app()->getLocale();
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
@endphp

<div class="min-h-screen bg-gray-50">
    <x-manager.layout>
        <div class="p-6 space-y-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('filament.UserResource.pluralLabel') }}</h1>
                </div>
            </div>

            {{-- 筛选器 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- 搜索 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            placeholder="{{ __('filament.user.name') }} / {{ __('filament.user.email') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    {{-- 用户组筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.user.user_group') }}</label>
                        <select wire:model.live="userGroupIdFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            @foreach($userGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 邮箱验证筛选 --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('filament.user.email_verified') }}</label>
                        <select wire:model.live="emailVerifiedFilter"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">{{ __('app.all') }}</option>
                            <option value="verified">{{ __('filament.user.email_verified') }}</option>
                            <option value="unverified">{{ __('filament.user.email_unverified') }}</option>
                        </select>
                    </div>

                    {{-- 重置按钮 --}}
                    <div class="flex items-end">
                        <button wire:click="resetFilters" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            {{ __('app.reset') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- 用户列表 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.avatar') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.email') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.user_group') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.email_verified') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('filament.user.orders_count') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('orders.created_at') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('app.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->getFirstMediaUrl('avatars'))
                                            <img src="{{ $user->getFirstMediaUrl('avatars') }}" alt="{{ $user->name }}" 
                                                class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                                                <span class="text-teal-600 font-semibold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $groupTranslation = $user->userGroup?->userGroupTranslations
                                                ->where('language_id', $lang?->id)
                                                ->first()
                                                ?? $user->userGroup?->userGroupTranslations->first();
                                        @endphp
                                        <div class="text-sm text-gray-900">
                                            {{ $groupTranslation?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->email_verified_at)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('filament.user.email_verified') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ __('filament.user.email_unverified') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-gray-900">{{ $user->orders_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ locaRoute('manager.users.show', ['user' => $user->id]) }}" 
                                           class="text-teal-600 hover:text-teal-900">
                                            {{ __('app.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('app.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 分页 --}}
                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-manager.layout>
</div>

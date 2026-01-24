@php
    $isEdit = $userId !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('users', $isEdit ? __('app.edit') : __('app.create'), __('manager.users.label'), locaRoute('manager.users'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="users" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.users.label') }}
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
                                        :label="__('manager.user.avatar')"
                                        error="avatar"
                                        :help="__('manager.user.avatar_helper')"
                                    />
                                </div>

                                {{-- 名称 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.user.name')"
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
                                    :label="__('manager.user.email')"
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

                                {{-- 用户组 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.user.user_group')"
                                    labelFor="userGroupId"
                                    error="userGroupId"
                                    :help="__('manager.user.user_group_helper')"
                                >
                                    <x-widgets.select 
                                        id="userGroupId"
                                        wire="userGroupId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($userGroups)->map(function($userGroup) use ($lang) {
                                            $translation = $userGroup->userGroupTranslations->where('language_id', $lang?->id)->first();
                                            $userGroupName = $translation ? $translation->name : ($userGroup->userGroupTranslations->first() ? $userGroup->userGroupTranslations->first()->name : $userGroup->id);
                                            return ['value' => $userGroup->id, 'label' => $userGroupName];
                                        })->toArray()]"
                                        error="userGroupId"
                                    />
                                </x-widgets.form-field>

                                {{-- 邮箱验证时间 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.user.email_verified_at')"
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
                            </div>
                        </div>

                        {{-- 密码 --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.user.password') }}</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 密码 --}}
                                <x-widgets.form-field 
                                    :label="__('manager.user.password') . (!$isEdit ? '' : ' (留空则不修改)')"
                                    labelFor="password"
                                    :required="!$isEdit"
                                    error="password"
                                    :help="__('manager.user.password_helper')"
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
                                    :label="__('manager.user.password_confirmation')"
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
                            </div>
                        </div>

                        {{-- 操作按钮 --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.users') }}" wire:navigate 
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

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} {{ __('manager.users.label') }}" keywords="{{ __('manager.users.label') }}" />

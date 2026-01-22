@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('notifications', __('notifications.title'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="notifications" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('notifications.my_notifications') }}</h1>
                    @if(auth('manager')->user()->unreadNotifications->count() > 0)
                        <button wire:click="markAllAsRead" 
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-300 rounded-lg hover:bg-teal-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('notifications.mark_all_as_read') }}
                        </button>
                    @endif
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                <div class="space-y-4">
                    @if($notifications->isEmpty())
                        <!-- 空状态 -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
                            <div class="max-w-md mx-auto">
                                <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <h3 class="mt-6 text-xl font-semibold text-gray-900">{{ __('notifications.no_notifications') }}</h3>
                                <p class="mt-2 text-sm text-gray-500">{{ __('notifications.no_notifications_desc') }}</p>
                            </div>
                        </div>
                    @else
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = $notification->unread();
                            @endphp

                            <div class="bg-white rounded-xl shadow-sm border {{ $isUnread ? 'border-teal-300 bg-teal-50/30' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-shadow">
                                <div class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            @if($isUnread)
                                                <div class="w-3 h-3 bg-teal-500 rounded-full mt-2"></div>
                                            @else
                                                <div class="w-3 h-3 bg-gray-300 rounded-full mt-2"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex-1">
                                                    <h4 class="text-base font-semibold text-gray-900 mb-1">
                                                        {{ $data['title'] ?? __('notifications.notification') }}
                                                    </h4>
                                                    <p class="text-sm text-gray-600 mb-2">
                                                        {{ $data['message'] ?? '' }}
                                                    </p>
                                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                        @if(isset($data['order_no']))
                                                            <span class="text-teal-600">订单号: {{ $data['order_no'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($isUnread)
                                                        <button wire:click="markAsRead('{{ $notification->id }}')" 
                                                                class="text-xs text-teal-600 hover:text-teal-700 px-2 py-1 rounded hover:bg-teal-50 transition-colors">
                                                            {{ __('notifications.mark_as_read') }}
                                                        </button>
                                                    @endif
                                                    <button wire:click="deleteNotification('{{ $notification->id }}')" 
                                                            wire:confirm="{{ __('notifications.confirm_delete') }}"
                                                            class="text-xs text-red-600 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                                        {{ __('app.delete') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- 分页 -->
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('notifications.title') }}" />

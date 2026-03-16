@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('after_sales', __('manager.after_sales.label'));
@endphp

<div class="min-h-[70vh] mb-10 ">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />

        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="orders" />

            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.after_sales.label') }}</h1>
                </div>

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input
                                type="text"
                                wire="live.debounce.300ms=search"
                                placeholder="售后ID、订单号、用户名称、邮箱"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.after_sales.type') }}</x-widgets.label>
                            <x-widgets.select
                                wire="live=filterType"
                                :options="[
                                    ['value' => '', 'label' => __('app.all')],
                                    ['value' => 'refund_only', 'label' => '仅退款'],
                                    ['value' => 'refund_and_return', 'label' => '退货退款'],
                                    ['value' => 'exchange', 'label' => '换货'],
                                ]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.after_sales.status') }}</x-widgets.label>
                            <x-widgets.select
                                wire="live=filterStatus"
                                :options="[
                                    ['value' => '', 'label' => __('app.all')],
                                    ['value' => 'pending', 'label' => '待审核'],
                                    ['value' => 'approved', 'label' => '已通过'],
                                    ['value' => 'rejected', 'label' => '已拒绝'],
                                    ['value' => 'in_return', 'label' => '退货中'],
                                    ['value' => 'completed', 'label' => '已完成'],
                                    ['value' => 'canceled', 'label' => '已取消'],
                                ]"
                            />
                        </div>
                    </div>
                        <div class="mt-4">
                            <x-widgets.button
                                wire:click="resetFilters"
                                variant="secondary"
                            >
                                {{ __('app.reset') }}
                            </x-widgets.button>
                        </div>
                </div>

                {{-- 列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.order_no') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.user') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.product') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.quantity') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.applied_at') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('manager.after_sales.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($afterSales as $afterSale)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $afterSale->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $afterSale->order?->order_no ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($afterSale->order?->user)
                                                {{ $afterSale->order->user->name }}<br>
                                                <span class="text-xs text-gray-500">{{ $afterSale->order->user->email }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $afterSale->product?->slug ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @php
                                                $typeLabels = [
                                                    'refund_only' => __('manager.after_sales.type_refund_only'),
                                                    'refund_and_return' => __('manager.after_sales.type_refund_and_return'),
                                                    'exchange' => __('manager.after_sales.type_exchange'),
                                                ];
                                            @endphp
                                            {{ $typeLabels[$afterSale->type] ?? $afterSale->type }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @php
                                                $statusLabels = [
                                                    'pending' => __('manager.after_sales.status_pending'),
                                                    'approved' => __('manager.after_sales.status_approved'),
                                                    'rejected' => __('manager.after_sales.status_rejected'),
                                                    'in_return' => __('manager.after_sales.status_in_return'),
                                                    'completed' => __('manager.after_sales.status_completed'),
                                                    'canceled' => __('manager.after_sales.status_canceled'),
                                                ];
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-blue-100 text-blue-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'in_return' => 'bg-purple-100 text-purple-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'canceled' => 'bg-gray-100 text-gray-800',
                                                ];
                                                $status = $afterSale->status;
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$status] ?? $status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $afterSale->quantity }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $afterSale->created_at?->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right space-y-1">
                                            @if($afterSale->status === 'pending')
                                                <x-widgets.button
                                                    size="xs"
                                                    variant="success-outline"
                                                    wire:click="openDialog('approve', {{ $afterSale->id }})"
                                                    class="mr-1"
                                                >
                                                    {{ __('manager.after_sales.action_approve') }}
                                                </x-widgets.button>
                                                <x-widgets.button
                                                    size="xs"
                                                    variant="danger-outline"
                                                    wire:click="openDialog('reject', {{ $afterSale->id }})"
                                                >
                                                    {{ __('manager.after_sales.action_reject') }}
                                                </x-widgets.button>
                                            @elseif(in_array($afterSale->status, ['approved','in_return']))
                                                <x-widgets.button
                                                    size="xs"
                                                    variant="primary-outline"
                                                    wire:click="openDialog('complete', {{ $afterSale->id }})"
                                                    class="mr-1"
                                                >
                                                    {{ __('manager.after_sales.action_complete') }}
                                                </x-widgets.button>
                                                <x-widgets.button
                                                    size="xs"
                                                    variant="secondary"
                                                    wire:click="openDialog('cancel', {{ $afterSale->id }})"
                                                >
                                                    {{ __('manager.after_sales.action_cancel') }}
                                                </x-widgets.button>
                                            @else
                                            <span class="text-xs text-gray-400">{{ __('manager.after_sales.no_actions') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">
                                            {{ __('manager.after_sales.empty') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($afterSales->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $afterSales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- 审核备注弹窗 --}}
        @if($this->showDialog)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ __('manager.after_sales.remarks_label') }}
                        </h2>
                        <button class="text-gray-400 hover:text-gray-600" wire:click="$set('showDialog', false)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <p class="text-xs text-gray-500">
                            {{ __('manager.after_sales.remarks_placeholder') }}
                        </p>
                        <x-widgets.textarea
                            wire="dialogRemarks"
                            rows="4"
                            placeholder="{{ __('manager.after_sales.remarks_placeholder') }}"
                        />
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-end gap-2 bg-gray-50 rounded-b-xl">
                        <x-widgets.button
                            type="button"
                            variant="secondary"
                            wire:click="$set('showDialog', false)"
                        >
                            {{ __('app.cancel') }}
                        </x-widgets.button>
                        <x-widgets.button
                            type="button"
                            wire:click="performAction"
                        >
                            {{ __('app.confirm') }}
                        </x-widgets.button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<x-seo-meta title="{{ __('manager.after_sales.label') }}" />


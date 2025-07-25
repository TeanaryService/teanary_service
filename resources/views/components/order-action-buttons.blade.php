@props(['order', 'showDetails' => false])

@php
    $btnClass = 'inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2';
@endphp

<div class="flex flex-col gap-2">
    @if ($order->status->canBeCancelled())
        <button
            wire:click="cancelOrder"
            wire:confirm="{{ __('orders.confirm_cancel') }}"
            class="{{ $btnClass }} bg-red-600 hover:bg-red-700 focus:ring-red-500">
            <x-heroicon-o-trash class="w-4 h-4" />
            {{ __('orders.cancel_order') }}
        </button>
    @endif

    @if ($order->status->canBePaid())
        <a href="{{ locaRoute('payment.checkout', ['orderId' => $order]) }}"
           class="{{ $btnClass }} bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">
           <x-heroicon-o-lock-closed class="w-4 h-4" />
            {{ __('orders.pay_now') }}
        </a>
    @endif

    {{-- @if ($order->status->canRequestAfterSale())
        <a href="{{ locaRoute('refunds.create', ['order' => $order]) }}"
           class="{{ $btnClass }} bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 14l6-6M15 14V8H9"></path>
            </svg>
            {{ __('orders.request_refund') }}
        </a>
    @endif --}}

    @if ($showDetails)
        <a href="{{ locaRoute('user.orders.show', ['order' => $order]) }}"
           class="{{ $btnClass }} bg-teal-600 hover:bg-teal-700 focus:ring-teal-500">
            <x-heroicon-o-eye class="w-4 h-4" />
            {{ __('orders.view_details') }}
        </a>
    @endif
</div>
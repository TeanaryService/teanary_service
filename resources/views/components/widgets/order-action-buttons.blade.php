@props(['order', 'showDetails' => false])

@php
    $btnClass = getOrderActionButtonClass();
@endphp

<div class="flex flex-col gap-2">
    @if ($order->status->canBePaid())
        <a target="_blank" href="{{ locaRoute('payment.checkout', ['orderId' => $order]) }}" wire:navigate
            class="{{ $btnClass }} bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">
            <x-heroicon-o-lock-closed class="w-4 h-4" />
            {{ __('orders.pay_now') }}
        </a>
    @endif

    @if ($order->status->canRequestAfterSale())
        <p class="text-xs">{{ __('orders.contact_email', ['email' => 'hello@teanary.com']) }}</p>
    @endif

    @if ($order->status->canBeCancelled())
        <button wire:click="cancelOrder" wire:confirm="{{ __('orders.confirm_cancel') }}"
            class="{{ $btnClass }} bg-red-600 hover:bg-red-700 focus:ring-red-500">
            <x-heroicon-o-trash class="w-4 h-4" />
            {{ __('orders.cancel_order') }}
        </button>
    @endif

    @if ($showDetails)
        <a href="{{ locaRoute('auth.order-detail', ['order' => $order]) }}" wire:navigate
            class="{{ $btnClass }} bg-teal-600 hover:bg-teal-700 focus:ring-teal-500">
            <x-heroicon-o-eye class="w-4 h-4" />
            {{ __('orders.view_details') }}
        </a>
    @endif
</div>

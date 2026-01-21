<x-payment.status
    type="warning"
    :title="__('payment.cancel_title')"
    :message="__('payment.cancel_message')"
    :icon="['heroicon-o-exclamation-circle', 'text-yellow-500']"
    :button="[
        'label' => __('payment.view_order'),
        'url' => locaRoute('auth.orders'),
        'class' => 'bg-gray-700 hover:bg-gray-800'
    ]"
/>

<x-seo-meta :title="__('payment.cancel_title')" />

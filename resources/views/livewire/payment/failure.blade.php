<x-payment.status
    type="error"
    :title="__('payment.failed_title')"
    :message="__('payment.failed_message')"
    :icon="['heroicon-o-x-circle', 'text-red-500']"
    :button="[
        'label' => __('payment.view_order'),
        'url' => locaRoute('auth.orders'),
        'class' => 'bg-red-600 hover:bg-red-700'
    ]"
/>

<x-seo-meta :title="__('payment.failed_title')" />
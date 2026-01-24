<x-payment.status
    type="success"
    :title="__('payment.success_title')"
    :message="__('payment.success_message')"
    :icon="['heroicon-o-check-circle', 'text-teal-500']"
    :button="[
        'label' => __('payment.back_home'),
        'url' => locaRoute('home'),
        'class' => 'bg-teal-600 hover:bg-teal-700'
    ]"
/>

<x-seo-meta :title="__('payment.success_title')" />

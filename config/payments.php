<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | 支付网关配置
    |
    */

    'paypal' => [
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
            'secret' => env('PAYPAL_SANDBOX_SECRET', ''),
        ],
        'production' => [
            'client_id' => env('PAYPAL_PRODUCTION_CLIENT_ID', ''),
            'secret' => env('PAYPAL_PRODUCTION_SECRET', ''),
        ],
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Engine Push Services
    |--------------------------------------------------------------------------
    |
    | Configuration for search engine indexing services such as Bing and Google.
    |
    */

    'bing' => [
        'enabled' => env('BING_PUSH_ENABLED', false),
        'api_key' => env('BING_API_KEY'),
        'site' => env('BING_SITE'),
        'api' => 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrl',
    ],

    'google' => [
        'enabled' => env('GOOGLE_PUSH_ENABLED', false),
        'api_key' => env('GOOGLE_API_KEY'),
        'site' => env('GOOGLE_SITE'),
        'api' => 'https://indexing.googleapis.com/v3/urlNotifications:publish',
    ],

];

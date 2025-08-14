<?php

return [
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

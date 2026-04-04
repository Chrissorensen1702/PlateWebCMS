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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'laravel_cloud' => [
        'token' => env('LARAVEL_CLOUD_API_TOKEN'),
        'environment_id' => env('LARAVEL_CLOUD_ENVIRONMENT_ID'),
        'dashboard_url' => env('LARAVEL_CLOUD_DASHBOARD_URL'),
        'metrics_period' => env('LARAVEL_CLOUD_METRICS_PERIOD', '24h'),
        'projects' => [
            [
                'label' => env('LARAVEL_CLOUD_BOOKINGSYSTEM_LABEL', 'Bookingsystem'),
                'token' => env('LARAVEL_CLOUD_BOOKINGSYSTEM_API_TOKEN', env('LARAVEL_CLOUD_API_TOKEN')),
                'environment_id' => env('LARAVEL_CLOUD_BOOKINGSYSTEM_ENVIRONMENT_ID', env('LARAVEL_CLOUD_ENVIRONMENT_ID')),
                'dashboard_url' => env('LARAVEL_CLOUD_BOOKINGSYSTEM_DASHBOARD_URL', env('LARAVEL_CLOUD_DASHBOARD_URL')),
                'metrics_period' => env('LARAVEL_CLOUD_BOOKINGSYSTEM_METRICS_PERIOD', env('LARAVEL_CLOUD_METRICS_PERIOD', '24h')),
            ],
            [
                'label' => env('LARAVEL_CLOUD_CMS_LABEL', 'CMS'),
                'token' => env('LARAVEL_CLOUD_CMS_API_TOKEN'),
                'environment_id' => env('LARAVEL_CLOUD_CMS_ENVIRONMENT_ID'),
                'dashboard_url' => env('LARAVEL_CLOUD_CMS_DASHBOARD_URL'),
                'metrics_period' => env('LARAVEL_CLOUD_CMS_METRICS_PERIOD', env('LARAVEL_CLOUD_METRICS_PERIOD', '24h')),
            ],
        ],
    ],

];

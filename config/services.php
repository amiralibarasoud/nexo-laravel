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

    'smsir' => [
        'api_key' => env('SMSIR_API_KEY'),
        'otp_template_id' => env('SMSIR_OTP_TEMPLATE_ID', 238380),
    ],

    'zarinpal' => [
        'enabled' => env('ZARINPAL_ENABLED', false),
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => env('ZARINPAL_SANDBOX', true),
    ],

    'zibal' => [
        'merchant' => env('ZIBAL_MERCHANT', '6a1ec5ceccc7cc942bdbec8f'),
    ],

];

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

    // ⚠️ প্ল্যাটফর্ম-লেভেল SMS (রেজিস্ট্রেশন OTP যাচাইয়ের জন্য) — Onecodesoft
    // Bulk SMS অ্যাকাউন্ট। এটা প্রতিষ্ঠানের নিজস্ব per-tenant SMS গেটওয়ে
    // সেটিংস (IntegrationSetting মডেল) থেকে আলাদা।
    'bulksms' => [
        'endpoint' => env('BULKSMS_ENDPOINT', 'https://sms.ocs-api.top/api/send-sms'),
        'api_key' => env('BULKSMS_API_KEY'),
        'sender_id' => env('BULKSMS_SENDER_ID'),
    ],

];

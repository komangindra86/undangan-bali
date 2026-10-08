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

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
        // Gift provider for every invitation: midtrans, xendit or ipaymu. Kept under this key for existing deployments.
        'payment_provider' => env('WEDDING_GIFT_PAYMENT_PROVIDER', 'midtrans'),
    ],

    'ipaymu' => [
        'va' => env('IPAYMU_VA'),
        'api_key' => env('IPAYMU_API_KEY'),
        'sandbox' => (bool) env('IPAYMU_SANDBOX', true),
        'qris_channel' => env('IPAYMU_QRIS_CHANNEL', 'mpm'),
        'expiry_hours' => (int) env('IPAYMU_QRIS_EXPIRY_HOURS', 24),
        // iPaymu requires a buyer e-mail and phone; guests only give a name, so these fill the gaps.
        'fallback_email' => env('IPAYMU_FALLBACK_EMAIL'),
        'fallback_phone' => env('IPAYMU_FALLBACK_PHONE', env('CUSTOM_INVITATION_WHATSAPP', '081000000000')),
    ],

    'google' => [
        'client_ids' => array_filter(array_map('trim', explode(',', (string) env('GOOGLE_CLIENT_IDS', '')))),
        'web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),
        'web_client_secret' => env('GOOGLE_WEB_CLIENT_SECRET'),
        'web_redirect_uri' => env(
            'GOOGLE_WEB_REDIRECT_URI',
            rtrim((string) env('APP_URL', 'http://localhost'), '/').'/auth/google/mobile/callback'
        ),
        'mobile_redirect_uri' => env('GOOGLE_MOBILE_REDIRECT_URI', 'undanganbali://auth/google'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS', base_path('firebase-service-account.json')),
        'token_uri' => env('FIREBASE_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
        'send_url' => env('FIREBASE_SEND_URL', 'https://fcm.googleapis.com/v1/projects/%s/messages:send'),
    ],

    'custom_invitation' => [
        'whatsapp_number' => env('CUSTOM_INVITATION_WHATSAPP'),
    ],

];

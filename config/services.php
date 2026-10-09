<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Google Drive — penyimpanan salinan berkas unggahan
    |--------------------------------------------------------------------------
    |
    | Integrasi bersifat opsional. Bila dimatikan atau kredensial tidak ada,
    | aplikasi tetap berjalan penuh dengan penyimpanan lokal.
    |
    */

    'google_drive' => [
        'enabled'        => env('GOOGLE_DRIVE_ENABLED', false),
        'folder_id'      => env('GOOGLE_DRIVE_FOLDER_ID'),
        'credentials'    => env('GOOGLE_DRIVE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
        'shared_drive'   => env('GOOGLE_DRIVE_SHARED_DRIVE_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PPDB — pengaturan portal pendaftaran
    |--------------------------------------------------------------------------
    */

    'ppdb' => [
        'wa_group_url' => env('WA_GROUP_URL'),
    ],

];

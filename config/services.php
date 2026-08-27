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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'holiday_api' => [
        'key' => env('HOLIDAY_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
        'indexing_key' => env('GOOGLE_INDEXING_CREDENTIALS_PATH', 'storage/app/google-credentials.json'),
        'indexing_credentials' => env('GOOGLE_INDEXING_CREDENTIALS_PATH'),
        'indexing_daily_quota' => env('GOOGLE_INDEXING_DAILY_QUOTA', 200),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
        'app_id' => env('FACEBOOK_APP_ID'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
    ],

    'ai' => [
        'groq' => env('GROQ_API_KEY'),
        'gemini' => env('GEMINI_API_KEY'),
        'cerebras' => env('CEREBRAS_API_KEY'),
        'mistral' => env('MISTRAL_API_KEY'),
        'huggingface' => env('HUGGINGFACE_API_KEY'),
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
    ],
    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'libreoffice' => [
        'binary' => env('LIBREOFFICE_BINARY_PATH', '/usr/bin/soffice'),
    ],

    'ffmpeg' => [
        'binary' => env('FFMPEG_BINARY', '/usr/bin/ffmpeg'),
        'ffprobe' => env('FFPROBE_BINARY', '/usr/bin/ffprobe'),
    ],

    'cloudconvert' => [
        'api_key' => env('CLOUDCONVERT_API_KEY'),
    ],

];

<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Third Party Services
     * |--------------------------------------------------------------------------
     * |
     * | This file is for storing the credentials for third party services such
     * | as Mailgun, Postmark, AWS and more. This file provides the de facto
     * | location for this type of information, allowing packages to have
     * | a conventional file to locate the various service credentials.
     * |
     */
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'multitexter' => [
        'email' => env('MULTITEXTER_EMAIL'),
        'password' => env('MULTITEXTER_PASSWORD'),
    ],
    'remita' => [
        'merchant_id' => env('REMITA_MERCHANT_ID'),
        'api_key' => env('REMITA_API_KEY'),
        'public_key' => env('REMITA_PUBLIC_KEY'),
        'base_url' => env('REMITA_BASE_URL'),
        'hostel_key' => env('REMITA_HOSTEL_KEY'),
        'hostel_description' => env('REMITA_HOSTEL_DESCRIPTION'),
        'school_fees_key' => env('REMITA_SCHOOL_FEES_KEY'),
        'post_utme_key' => env('REMITA_POST_UTME_KEY'),
        'post_utme_description' => env('REMITA_POST_UTME_DESCRIPTION'),
        'change_of_course_key' => env('REMITA_CHANGE_OF_COURSE_KEY'),
        'inter_transfer_key' => env('REMITA_INTER_TRANSFER_KEY'),
    ],
    'face_search' => [
        'python' => env('FACE_SEARCH_PYTHON', '/opt/pg-photo-venv/bin/python3'),
        'model_dir' => env('FACE_SEARCH_MODEL_DIR', storage_path('app/ai-models/face-search')),
        'threshold' => (float) env('FACE_SEARCH_THRESHOLD', 0.48),
        'min_margin' => (float) env('FACE_SEARCH_MIN_MARGIN', 0.045),
        'timeout' => (int) env('FACE_SEARCH_TIMEOUT', 45),
    ],
];

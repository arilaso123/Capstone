<?php

return [
    'bca_account' => env('BCA_MERCHANT_ACCOUNT', '8720-1928-33'),
    'bca_name' => env('BCA_MERCHANT_NAME', 'UYUY JASTIP PARFUM'),
    'payment_deadline_hours' => (int) env('PAYMENT_DEADLINE_HOURS', 24),
    'social' => [
        'whatsapp' => env('SOCIAL_WHATSAPP_URL'),
        'instagram' => env('SOCIAL_INSTAGRAM_URL'),
        'tiktok' => env('SOCIAL_TIKTOK_URL'),
        'threads' => env('SOCIAL_THREADS_URL'),
    ],
];

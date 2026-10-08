<?php

return [
    'contact' => [
        'email' => env('BLA_CONTACT_EMAIL', 'hello@balilivingassist.com'),
        'whatsapp_number' => env('BLA_WHATSAPP_NUMBER', '+62 851-7332-3293'),
    ],
    'social' => [
        'instagram' => env('BLA_INSTAGRAM_URL', 'https://www.instagram.com/balilivingassist/'),
        'tiktok' => env('BLA_TIKTOK_URL', 'https://www.tiktok.com/@balilivingassist'),
        'threads' => env('BLA_THREADS_URL', 'https://www.threads.com/@balilivingassist'),
        'facebook' => env('BLA_FACEBOOK_URL', 'https://www.facebook.com/share/1M2ia8tVgB/?mibextid=wwXIfr'),
        'whatsapp' => env('BLA_WHATSAPP_URL', 'https://wa.me/6285173323293'),
    ],
    'service_areas' => [
        'renovation_building',
        'service_maintenance',
        'procurement_supply',
    ],
];

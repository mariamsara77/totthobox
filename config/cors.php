<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth/*',
        'broadcasting/auth', // ← Reverb / Echo broadcasting-এর জন্য প্রয়োজনীয়
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://totthobox.com',
        'https://www.totthobox.com',
        'http://localhost:3000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
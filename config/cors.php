<?php

return [
    'paths' => ['api/*', 'csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],            // For dev only; lock down in prod
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

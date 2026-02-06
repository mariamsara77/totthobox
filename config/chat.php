<?php

return [
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'audio/mpeg',
        'audio/wav',
    ],

    'max_file_size' => 10240, // 10MB in KB

    'polling_interval' => 300, // milliseconds
];

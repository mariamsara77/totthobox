<?php

return [
    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | A cache directory must be configured for Blade compilation. Keep this
    | path independent of realpath() so a fresh checkout can bootstrap even
    | before runtime directories have been created.
    |
    */
    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),
];

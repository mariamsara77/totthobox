<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\GoogleIndexingServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\SearchServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    Google\GenerativeAI\Laravel\ServiceProvider::class,
    Spatie\Backup\BackupServiceProvider::class,
];

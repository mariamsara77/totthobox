<?php

namespace App\Providers;

use App\Search\GlobalSearchService;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind as singleton — one instance per request
        $this->app->singleton(GlobalSearchService::class);
    }

    public function boot(): void
    {
        // Register the artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ConfigureSearchCommand::class,
            ]);
        }
    }
}

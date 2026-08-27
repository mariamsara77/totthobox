<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pulse Access Gate
        Gate::define('viewPulse', fn (User $user) => $user->id === 1);

        Event::listen(NotificationSendingFailed::class, LogWebPushFailure::class);

        // Eloquent Strict Mode (Only for Non-Production)
        $this->configureEloquent();
    }

    /**
     * Configure Eloquent behavior for development.
     */
    private function configureEloquent(): void
    {
        if (! $this->app->isProduction()) {
            // Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();
        }
    }
}

<?php

namespace App\Providers;

use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton pattern for TranslationService
        $this->app->singleton(TranslationService::class, fn () => new TranslationService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Pulse Access Gate
        Gate::define('viewPulse', fn (User $user) => $user->id === 1);

        Auth::resolved(function ($auth) {
            $auth->user()?->loadMissing(['roles', 'permissions', 'media']);
        });

        // Eloquent Strict Mode (Only for Non-Production)
        $this->configureEloquent();
    }

    /**
     * Configure Eloquent behavior for development.
     */
    private function configureEloquent(): void
    {
        if (! $this->app->isProduction()) {
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes(); // আরও সিকিউর করার জন্য এটি যোগ করা হয়েছে
        }
    }
}

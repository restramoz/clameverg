<?php

namespace App\Providers;

use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Reuse cached in-memory indexes across a request.
        $this->app->singleton(SchemeService::class);
        $this->app->singleton(RegionalService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

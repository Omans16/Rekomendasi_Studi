<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FlaskRecommendationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FlaskRecommendationService::class, function () {
            return new FlaskRecommendationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\JwtAuthService;
use App\Http\Middleware\JwtAuthMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JwtAuthService::class, function ($app) {
            return new JwtAuthService();
        });
  
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

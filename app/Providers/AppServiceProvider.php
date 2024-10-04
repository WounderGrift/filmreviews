<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->loadViewsFrom(resource_path('views/main'), 'main');
        $this->loadTranslationsFrom(resource_path('lang/main'), 'main');
        $this->loadViewsFrom(resource_path('views/layouts'), 'layouts');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

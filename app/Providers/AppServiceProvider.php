<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{


    public function boot(): void
    {
        if (config('app.env') === 'production' && env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}

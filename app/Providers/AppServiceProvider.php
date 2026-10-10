<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
          URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Restrict Reverb WebSocket origins in production without editing vendor config.
        $origins = array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('REVERB_ALLOWED_ORIGINS', '*'))
        )));

        if ($origins !== []) {
            config()->set('reverb.apps.apps.0.allowed_origins', $origins);
        }
    }
}

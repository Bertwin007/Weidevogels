<?php

namespace App\Providers;

use App\Models\Observation;
use App\Policies\ObservationPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Observation::class, ObservationPolicy::class);

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, 'nl_NL.UTF-8', 'nl_NL', 'nl', 'Dutch');
    }
}

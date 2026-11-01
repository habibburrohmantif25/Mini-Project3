<?php

namespace App\Providers;

use App\Models\IncidentReport;
use App\Observers\IncidentReportObserver;
use App\Policies\IncidentReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        IncidentReport::observe(IncidentReportObserver::class);

        Gate::policy(IncidentReport::class, IncidentReportPolicy::class);
    }
}

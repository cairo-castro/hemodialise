<?php

namespace App\Providers;

use App\Models\ChemicalDisinfection;
use App\Models\CleaningChecklist;
use App\Models\CleaningControl;
use App\Models\SafetyChecklist;
use App\Models\Patient;
use App\Observers\ChemicalDisinfectionObserver;
use App\Observers\CleaningChecklistObserver;
use App\Observers\CleaningControlObserver;
use App\Observers\SafetyChecklistObserver;
use App\Observers\PatientObserver;
use Illuminate\Support\Facades\URL;
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
        // Força HTTPS em produção (quando atrás de proxy/load balancer)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // Força HTTPS para todos os assets quando APP_URL é HTTPS
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Register observers for automatic notifications
        SafetyChecklist::observe(SafetyChecklistObserver::class);
        Patient::observe(PatientObserver::class);
        CleaningControl::observe(CleaningControlObserver::class);
    }
}

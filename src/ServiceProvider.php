<?php

namespace Daikazu\GA4EventTracking;

use Daikazu\GA4EventTracking\Console\MakeGA4EventCommand;
use Daikazu\GA4EventTracking\Events\BroadcastEvent;
use Daikazu\GA4EventTracking\Events\EventBroadcaster;
use Daikazu\GA4EventTracking\Http\ClientIdRepository;
use Daikazu\GA4EventTracking\Http\ClientIdSession;
use Daikazu\GA4EventTracking\Http\StoreClientIdInSession;
use Daikazu\GA4EventTracking\Listeners\DispatchAnalyticsJob;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'daikazu');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ga4-event-tracking');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        Event::listen(ShouldBroadcastToAnalytics::class, DispatchAnalyticsJob::class);

        Blade::directive('sendClientID', function () {
            return "<?php echo view('ga4-event-tracking::sendClientID'); ?>";
        });

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ga4-event-tracking');

        $this->app->singleton(EventBroadcaster::class, BroadcastEvent::class);
        $this->registerClientId();
        $this->registerAnalytics();
        $this->registerRoute();


        // Register the service the package provides.
//        $this->app->singleton('laravel-ga4-event-tracking', function ($app) {
//            return new LaravelGa4EventTracking;
//        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['ga4-event-tracking'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('ga4-event-tracking.php'),
        ], 'ga4-event-tracking.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/daikazu'),
        ], 'ga4-event-tracking.views');


        // Registering package commands.
         $this->commands([
             MakeGA4EventCommand::class
         ]);
    }



    private function registerAnalytics()
    {
        $this->app->bind('ga4', function () {
            return new GA4();


//            return tap(new GA4(), function (GA4 $GA4) {
//
////                $GA4->setProtocolVersion(1)->setTrackingId(
////                    config('ga4-event-tracking.measurement_id')
////                );
////
////                if (config('ga4-event-tracking.anonymize_ip')) {
////                    $GA4->setAnonymizeIp(1);
////                }
//
//            });
        });
    }




    private function registerClientId()
    {
        $this->app->singleton(ClientIdRepository::class, ClientIdSession::class);

        $this->app->bind('ga4-event-tracking.client-id', function () {
            return $this->app->make(ClientIdSession::class)->get();
        });

        $this->app->singleton(ClientIdSession::class, function () {
            return new ClientIdSession(
                $this->app->make('session.store'),
                config('ga4-event-tracking.client_id_session_key')
            );
        });
    }

    private function registerRoute()
    {
        if ($httpUri = config('ga4-event-tracking.http_uri')) {
            Route::post($httpUri, StoreClientIdInSession::class)->middleware('web');
        }
    }

}

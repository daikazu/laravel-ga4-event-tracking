<?php

namespace Daikazu\GA4;

use Daikazu\GA4\Events\BroadcastEvent;
use Daikazu\GA4\Events\EventBroadcaster;
use Daikazu\GA4\Http\ClientIdRepository;
use Daikazu\GA4\Http\ClientIdSession;
use Daikazu\GA4\Listeners\DispatchAnalyticsJob;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GA4ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ga4-event-tracking')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web');
    }

    public function packageRegistered()
    {
        $this->app->bind('ga4', function () {
            return new GA4();
        });

        $this->app->singleton(EventBroadcaster::class, BroadcastEvent::class);

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

    public function packageBooted()
    {
//        Event::listen(ShouldBroadcastToAnalytics::class, DispatchAnalyticsJob::class);

        Blade::directive('sendClientID', function () {
            return "<?php echo view('ga4-event-tracking::sendClientID'); ?>";
        });
    }
}

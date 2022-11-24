# Laravel Google Analytics 4 Measurement Protocol EventTracking

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/laravel-ga4-event-tracking.svg?style=flat-square)](https://packagist.org/packages/daikazu/laravel-ga4-event-tracking)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/daikazu/laravel-ga4-event-tracking/run-tests?label=tests)](https://github.com/daikazu/laravel-ga4-event-tracking/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/daikazu/laravel-ga4-event-tracking/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/daikazu/laravel-ga4-event-tracking/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/laravel-ga4-event-tracking.svg?style=flat-square)](https://packagist.org/packages/daikazu/laravel-ga4-event-tracking)

A Laravel package to use [Measurement Protocol for Google Analytics 4](https://developers.google.com/analytics/devguides/collection/protocol/ga4).

## Installation

1) Install the package via composer:

```bash
composer require daikazu/laravel-ga4-event-tracking
```

2) Set `MEASUREMENT_ID` and `MEASUREMENT_PROTOCOL_API_SECRET` in your .env file.
   You can find this information from: Google Analytics > Admin > Data Streams > [Select Site] > Measurement Protocol API secrets

3) Optional: You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-ga4-event-tracking-config"
```
This is the contents of the published config file:

```php
return [
    /**
     * Your GA Measurement ID.
     * https://support.google.com/analytics/answer/1008080
     */
    'measurement_id' => env('GA4_MEASUREMENT_ID'),

    'api_secret' => env('GA4_MEASUREMENT_PROTOCOL_API_SECRET', null),

    /**
     * The session key to store the Client ID.
     */
    'client_id_session_key' => 'ga4-event-tracking-client-id',

    /**
     * HTTP URI to post the Client ID to (from the Blade Directive).
     */
    'http_uri' => '/gaid',

    /*
    * This queue will be used to perform the API calls to GA.
    * Leave empty to use the default queue.
    */
    'queue_name' => '',

    /**
     * Send the ID of the authenticated user to GA.
     */
    'send_user_id' => false,
];
```

4) `client_id` is required to post an event to Google Analytics. This package provides a Blade directive which you can put in your layout file after the Google Analytics Code tracking code. It makes a POST request to the backend to store the client id in the session which is later used to post events to Google Analytics 4.

```html
<!-- Google Analytics Code -->
@sendClientID
<!-- </head> -->
```
The other option is to call the `setClientId($clientId)` method on the `GA4` facade everytime before calling the `sendEvent()` method.

## Usage

This package provides two ways to send events to Google Analytics 4.

1) ### Directly

Sending event directly is as simple as calling the `sendEvent($eventData)` method on the `GA4` facade from anywhere in your backend to post event to Google Analytics 4. `$eventData` contains the name and params of the event as per this [reference page](https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference/events#login). For example:

```php
GA4::sendEvent([
    'name' => 'login',
    'params' => [
        'method' => 'Google',
    ],
]);
```

`sendEvent()` method will return an array with the status of the request.


2) ### Broadcast events to Google Analytics 4 via the Laravel Event System

Add the `ShouldBroadcastToAnalytics` interface to your event, and you're ready! You don't have to manually bind any listeners.

``` php
<?php
namespace App\Events;

use App\Order;
use Daikazu\GA4\ShouldBroadcastToAnalytics;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWasCreated implements ShouldBroadcastToAnalytics
{
    use Dispatchable, SerializesModels;
    public $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
```

There are two additional methods that lets you customize the call to Google Analytics 4.

With the `broadcastGA4EventAs` method you can customize the name of the [Event Action](https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference#eventAction). By default, we use the class name with the class's namespace removed. This method gives you access to the underlying `GA4` class as well.

With the `withGA4Parameters` method you can set the parameters GA4 Event.


``` php
<?php
namespace App\Events;
use App\Order;
use Daikazu\GA4\ShouldBroadcastToAnalytics;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWasCreated implements ShouldBroadcastToAnalytics
{
    use Dispatchable, SerializesModels;
    public $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    
    public function withGA4Parameters(GA4 $ga4){
        return [
            'method' => 'order_form',
            'value' => $this->order->sum_in_cents / 100
        ];
    }


    public function broadcastGA4EventAs(GA4 $ga4)
    {
        return 'CustomEventAction';
    }

}
```


### Handle framework and 3rd-party events

If you want to handle events where you can't add the `ShouldBroadcastToAnalytics` interface, you can manually register them in your `EventServiceProvider` using the `DispatchAnalyticsJob` listener.

```php
<?php
namespace App\Providers;

use Daikazu\GA4\Listeners\DispatchAnalyticsJob;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
class EventServiceProvider extends ServiceProvider

{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            DispatchAnalyticsJob::class,
        ],
    ];
}
```

### Debugging Mode

You can also enable [debugging mode](https://developers.google.com/analytics/devguides/collection/protocol/ga4/validating-events) by calling `enableDebugging()` method before calling the `sendEvent()` method. Like so - `GA4::enableDebugging()->sendEvent($eventData)`. The `sendEvent()` method will return the response (array) from Google Analytics request in that case.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mike Wall](https://github.com/daikazu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

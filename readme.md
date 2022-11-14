# Laravel Google Analytics 4 Measurement Protocol EventTracking

A Laravel package to use [Measurement Protocol for Google Analytics 4](https://developers.google.com/analytics/devguides/collection/protocol/ga4).

## Installation

1) Install package via Composer
``` bash
composer require daikazu/laravel-ga4-event-tracking
```
2) Set `MEASUREMENT_ID` and `MEASUREMENT_PROTOCOL_API_SECRET` in your .env file.
   You can find this information from: Google Analytics > Admin > Data Streams > [Select Site] > Measurement Protocol API secrets

3) Optional: You can publish the config file by running this command in your terminal/cmd:
``` bash
php artisan vendor:publish --tag=laravel-ga4-event-tracking-config
```
4) `client_id` is required to post an event to Google Analytics. This package provides a Blade directive which you can put in your layout file after the Google Analytics Code tracking code. It makes a POST request to the backend to store the client id in the session which is later used to post events to Google Analytics 4.
```html
<!-- Google Analytics Code -->
@sendClientID
<!-- </head> -->
```
The other option is to call the `setClientId($clientId)` method on the `GA4` facade everytime before calling the `sendEvent()` method.


## Customization

You can publish and run the migrations with:
``` bash
php artisan vendor:publish --tag="laravel-ga4-event-tracking-migrations"
php artisan migrate
```

Optionally, you can publish the views using
```bash
php artisan vendor:publish --tag="laravel-ga4-event-tracking-views"
```

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
use Daikazu\GA4EventTracking\ShouldBroadcastToAnalytics;
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
use Daikazu\GA4EventTracking\ShouldBroadcastToAnalytics;
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

use Daikazu\GA4EventTracking\Listeners\DispatchAnalyticsJob;
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



## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email daikazu@gmail.com instead of using the issue tracker.

## Credits

- [Mike Wall][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/daikazu/laravel-ga4-event-tracking.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/daikazu/laravel-ga4-event-tracking.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/daikazu/laravel-ga4-event-tracking
[link-downloads]: https://packagist.org/packages/daikazu/laravel-ga4-event-tracking
[link-author]: https://github.com/daikazu
[link-contributors]: ../../contributors

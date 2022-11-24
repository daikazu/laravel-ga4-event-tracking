<?php

namespace Daikazu\GA4;

use Daikazu\GA4\Exceptions\MissingClientIdException;
use Daikazu\GA4\Exceptions\ReservedEventNameException;

class GA4
{
    private string $clientId = '';

    private string $userId = '';

    private bool $debugging = false;

    private string $eventAction;

    private array $eventParams;

    public function __construct()
    {
        if (config('ga4-event-tracking.measurement_id') === null
            || config('ga4-event-tracking.api_secret') === null
        ) {
            throw new \Exception('Please set .env variables for Google GA4 4 Measurement Protocol.');
        }
    }

    /**
     * @param  string  $clientId
     * @return \Daikazu\GA4\GA4
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @param  string  $userId
     * @return GA4
     */
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function enableDebugging(): self
    {
        $this->debugging = true;

        return $this;
    }

    /**
     * @throws MissingClientIdException
     * @throws ReservedEventNameException
     */
    public function sendEvent(array $eventData): array
    {
        if (! $this->clientId && ! $this->clientId = session(config('ga4-event-tracking.client_id_session_key'))) {
            throw new MissingClientIdException;
        }

        $this->validateEvent($eventData);

        $response = Http::withOptions([
            'query' => [
                'measurement_id' => config('ga4-event-tracking.measurement_id'),
                'api_secret' => config('ga4-event-tracking.api_secret'),
            ],
        ])->post($this->getRequestUrl(), [
            'client_id' => $this->clientId,
            'events' => [$eventData],
        ]);

        if ($this->debugging) {
            return $response->json();
        }

        return [
            'status' => $response->successful(),
        ];
    }

    private function getRequestUrl(): string
    {
        $url = 'https://www.google-analytics.com';
        $url .= $this->debugging ? '/debug' : '';

        return $url.'/mp/collect';
    }

    /**
     * @param  string  $eventAction
     */
    public function setEventAction(string $eventAction): void
    {
        $this->eventAction = $eventAction;
    }

    /**
     * @param  array  $eventParams
     */
    public function setEventParams(array $eventParams): void
    {
        $this->eventParams = $eventParams;
    }

    /**
     * @throws MissingClientIdException
     * @throws ReservedEventNameException
     */
    public function sendAsSystemEvent(): void
    {
        $this->sendEvent([
            'name' => $this->eventAction,
            'params' => $this->eventParams,
        ]);
    }

    public function validateEvent($eventAction)
    {
        $reservedNames = [
            'ad_activeview',
            'ad_click',
            'ad_exposure',
            'ad_impression',
            'ad_query',
            'adunit_exposure',
            'app_clear_data',
            'app_install',
            'app_update',
            'app_remove',
            'app_background',
            'app_exception',
            'app_foreground',
            'app_notification_dismiss',
            'app_notification_foreground',
            'app_notification_open',
            'app_notification_receive',
            'app_uninstall',
            'app_update',
            'error',
            'first_open',
            'first_visit',
            'in_app_purchase',
            'notification_dismiss',
            'notification_foreground',
            'notification_open',
            'notification_receive',
            'os_update',
            'screen_view',
            'session_start',
            'user_engagement',
        ];

        if (gettype($eventAction) === 'string') {
            if (in_array($eventAction, $reservedNames)) {
                throw new ReservedEventNameException("The event name {$eventAction} is reserved for Google Analytics 4. Please use a different name.");
            }
        }

        if (gettype($eventAction) === 'array') {
            if (in_array($eventAction['name'], $reservedNames)) {
                throw new ReservedEventNameException("The event name {$eventAction['name']} is reserved for Google Analytics 4. Please use a different name.");
            }
        }

        return $eventAction;
    }
}

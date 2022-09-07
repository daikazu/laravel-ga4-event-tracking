<?php

namespace Daikazu\GA4EventTracking;

use Exception;
use Illuminate\Support\Facades\Http;

class GA4
{

    private string $clientId = '';
    private string $userId = '';
    private bool $debugging = false;
    private string $eventCategory = ''; // Event params
    private string $eventAction = '';



    public function __construct()
    {
        if (config('ga4-event-tracking.measurement_id') === null
            || config('ga4-event-tracking.api_secret') === null
        ){
            throw new \Exception('Please set .env variables for Google GA4 4 Measurement Protocol.');
        }
    }


    /**
     * @param  string  $clientId
     * @return GA4
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

    public function sendEvent(array $eventData): array
    {
        if (!$this->clientId && !$this->clientId = session(config('ga4-event-tracking.client_id_session_key'))) {
            throw new Exception('Please use the package provided blade directive or set client_id manually before posting an event.');
        }

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
            'status' => $response->successful()
        ];

    }


    private function getRequestUrl(): string
    {
        $url = 'https://www.google-analytics.com';
        $url .= $this->debugging ? '/debug' : '';

        return $url.'/mp/collect';
    }

    /**
     * @param  string  $eventCategory
     */
    public function setEventCategory(string $eventCategory): void
    {
        $this->eventCategory = $eventCategory;
    }

    /**
     * @param  string  $eventAction
     */
    public function setEventAction(string $eventAction): void
    {
        $this->eventAction = $eventAction;
    }

    /**
     * @throws Exception
     */
    public function sendAsSystemEvent(){

//        $this->enableDebugging();


        ray($this, $this->sendEvent([
            'name' => $this->eventAction,
//            'params' => [
//                'val1' => 'test1'
//            ],
        ]));
    }




}

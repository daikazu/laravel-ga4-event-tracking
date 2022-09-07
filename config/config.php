<?php

return [

    /**
     * Your GA Measurement ID.
     * https://support.google.com/analytics/answer/1008080
     */
    'measurement_id' => env('GA4_MEASUREMENT_ID'),

    'api_secret'  => env('GA4_MEASUREMENT_PROTOCOL_API_SECRET', null),


    /**
     * The session key to store the Client ID.
     */
    'client_id_session_key' => 'ga4-event-tracking-client-id',

    /**
     * HTTP URI to post the Client ID to (from the Blade Directive).
     */
    'http_uri'              => '/gaid',

    /*
    * This queue will be used to perform the API calls to GA.
    * Leave empty to use the default queue.
    */
    'queue_name'            => '',

    /**
     * Send the ID of the authenticated user to GA.
     */
    'send_user_id' => false,
];

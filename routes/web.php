<?php

use Daikazu\GA4\Http\StoreClientIdInSession;

if ($httpUri = config('ga4-event-tracking.http_uri')) {
    Route::post($httpUri, StoreClientIdInSession::class)->middleware('web');
}

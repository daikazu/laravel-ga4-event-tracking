<?php

namespace Daikazu\GA4EventTracking\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreClientIdInSession
{
    /**
     * Stores the posted Client ID in the session.
     */
    public function __invoke(Request $request, ClientIdSession $clientIsSession): JsonResponse
    {


        $data = $request->validate(['client_id' => 'required|string|max:255']);
        ray($data);

        $clientIsSession->update($data['client_id']);

        return response()->json();
    }
}

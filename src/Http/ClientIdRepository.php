<?php

namespace Daikazu\GA4EventTracking\Http;

interface ClientIdRepository
{
    public function update(string $clientId): void;

    public function get(): ?string;
}

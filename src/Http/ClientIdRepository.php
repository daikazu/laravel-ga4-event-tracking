<?php

namespace Daikazu\GA4\Http;

interface ClientIdRepository
{
    public function update(string $clientId): void;

    public function get(): ?string;
}

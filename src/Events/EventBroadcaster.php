<?php

namespace Daikazu\GA4\Events;

interface EventBroadcaster
{
    public function handle($event);

    public function withParameters(callable $callback): self;
}

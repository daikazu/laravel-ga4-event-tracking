<?php

namespace Daikazu\GA4EventTracking\Events;

use Daikazu\GA4EventTracking\GA4;

class BroadcastEvent implements EventBroadcaster
{
    private GA4 $GA4;

    public function __construct(GA4 $GA4)
    {
        $this->GA4 = $GA4;
    }

    public function withParameters(callable $callback): self
    {
        $callback($this->GA4);

        return $this;
    }

    public function handle($event): void
    {
        $eventAction = method_exists($event, 'broadcastGA4EventAs')
            ? $event->broadcastGA4EventAs($this->GA4)
            : str(class_basename($event))->snake()->toString();

        $this->GA4->setEventAction($eventAction);

        if (method_exists($event, 'withParameters')) {
            $event->withParameters($this->GA4);
        }

        $this->GA4->sendAsSystemEvent();
    }
}

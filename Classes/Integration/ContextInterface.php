<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Sentry\Event;

interface ContextInterface
{
    public function appliesToEvent(Event $event): bool;

    public function addToEvent(Event $event): void;
}

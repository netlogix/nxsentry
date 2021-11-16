<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Sentry\Event;

class BeforeEventListener
{
    public static function onBeforeSend(Event $event): ?Event
    {
        /** @var ContextInterface[] $integrations */
        $integrations = [
            new Typo3Context(),
            new RequestContext(),
            new CommandContext(),
            new UserContext(),
        ];

        foreach ($integrations as $integration) {
            if (!$integration->appliesToEvent($event)) {
                continue;
            }
            $integration->addToEvent($event);
        }

        return $event;
    }
}

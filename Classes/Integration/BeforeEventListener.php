<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Sentry\Event;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeEventListener
{
    public static function onBeforeSend(Event $event): Event
    {
        /** @var ContextInterface[] $integrations */
        $integrations = [
            new Typo3Context(),
            new RequestContext(),
            new CommandContext(),
            new UserContext(),
        ];

        try {
            $integrations = array_merge(
                $integrations,
                GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('nxsentry', 'integrations') ?? []
            );
        } catch (\Throwable $t) {
        }

        foreach ($integrations as $integration) {
            if (!$integration instanceof ContextInterface) {
                throw new \Exception(sprintf('%s must implement \Netlogix\Nxsentry\Integration\ContextInterface', get_class($integration)), 1653976300);
            }
            try {
                if (!$integration->appliesToEvent($event)) {
                    continue;
                }
                $integration->addToEvent($event);
            } catch (\Throwable $t) {
            }
        }

        return $event;
    }
}

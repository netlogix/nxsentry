<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Jean85\PrettyVersions;
use Sentry\Event;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

class Typo3Context implements ContextInterface
{
    public function appliesToEvent(Event $event): bool
    {
        return true;
    }

    public function addToEvent(Event $event): void
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $applicationType = ApplicationType::fromRequest($request)->isFrontend() ? 'frontend' : 'backend';
        $tags = array_merge($event->getTags(), [
            'typo3_version' => TYPO3_version,
            'application_type' => $applicationType,
            'application_context' => (string)Environment::getContext(),
            'application_version' => PrettyVersions::getRootPackageVersion()->getPrettyVersion(),
        ]);

        $event->setTags($tags);
    }
}

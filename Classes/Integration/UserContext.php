<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Sentry\Event;
use Sentry\UserDataBag;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserContext implements ContextInterface
{
    public function appliesToEvent(Event $event): bool
    {
        return !Environment::isCli();
    }

    public function addToEvent(Event $event): void
    {
        $user = $event->getUser() ?? new UserDataBag();
        $user->setIpAddress(GeneralUtility::getIndpEnv('REMOTE_ADDR'));
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $userType = ApplicationType::fromRequest($request)->isFrontend() ? 'frontend' : 'backend';
        /** @var UserAspect $userAspect */
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect($userType . '.user');
        if ($userAspect->isLoggedIn()) {
            $user->setId($userAspect->get('id'));
            $user->setUsername($userAspect->get('username'));
            $user->setMetadata('groups', implode(', ', $userAspect->getGroupNames()));
        }
        $event->setUser($user);
    }
}

<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Integration;

use Sentry\Event;
use Symfony\Component\Console\Input\ArgvInput;
use TYPO3\CMS\Core\Core\Environment;

class CommandContext implements ContextInterface
{

    public function appliesToEvent(Event $event): bool
    {
        return Environment::isCli();
    }

    public function addToEvent(Event $event): void
    {
        $input = new ArgvInput();
        $tags = array_merge($event->getTags(), [
            'typo3.command' => $input->getFirstArgument() ?? 'list',
        ]);
        $extra = array_merge($event->getExtra(), [
            'typo3.command' => $input->getFirstArgument() ?? 'list',
        ]);
        $event->setTags($tags);
        $event->setExtra($extra);
    }
}

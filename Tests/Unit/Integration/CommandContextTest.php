<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Tests\Integration;

use Netlogix\Nxsentry\Integration\CommandContext;
use Nimut\TestingFramework\Bootstrap\SystemEnvironmentBuilder;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Sentry\Event;

class CommandContextTest extends UnitTestCase
{

    /**
     * @var Event
     */
    private $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::createEvent();
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInCliContext(): void
    {
        $commandContext = new CommandContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        $this->assertTrue($commandContext->appliesToEvent($this->event));
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInFrontendContext(): void
    {
        $commandContext = new CommandContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $this->assertFalse($commandContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInAjaxContext(): void
    {
        $commandContext = new CommandContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_AJAX);
        $this->assertFalse($commandContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInBackendContext(): void
    {
        $commandContext = new CommandContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $this->assertFalse($commandContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function addTypo3CliCommandAsTagAndExtra(): void
    {
        $argvBackup = $_SERVER['argv'];
        $_SERVER['argv'] = ['.Vendor/bin/typo3coms', 'testCommand'];
        $commandContext = new CommandContext();
        $commandContext->addToEvent($this->event);

        $this->assertEquals($this->event->getTags(), ['typo3.command' => 'testCommand']);
        $this->assertEquals($this->event->getExtra(), ['typo3.command' => 'testCommand']);
        $_SERVER['argv'] = $argvBackup;
    }

}

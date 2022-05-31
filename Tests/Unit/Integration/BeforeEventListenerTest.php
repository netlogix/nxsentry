<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Tests\Integration;

use Netlogix\Nxsentry\Integration\BeforeEventListener;
use Netlogix\Nxsentry\Integration\ContextInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Sentry\Event;
use stdClass;

class BeforeEventListenerTest extends UnitTestCase
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
    public function addUserSpecificIntegrations(): void
    {
        $mockContext = $this->getMockBuilder(ContextInterface::class)
            ->addMethods([])
            ->getMock();

        $mockContext->method('appliesToEvent')->willReturn(true);

        $mockContext->expects($this->once())
            ->method('appliesToEvent');

        $mockContext->expects($this->once())
            ->method('addToEvent');

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['nxsentry'] = [
            'integrations' => [
                $mockContext
            ]
        ];

        BeforeEventListener::onBeforeSend($this->event);
    }

    /**
     * @test
     * @return void
     */
    public function throwExceptionWhenIntegrationIsNotInterfaceCompatible(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['nxsentry'] = [
            'integrations' => [
                new stdClass()
            ]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(1653976300);
        $this->expectExceptionMessage('stdClass must implement \Netlogix\Nxsentry\Integration\ContextInterface');
        BeforeEventListener::onBeforeSend($this->event);
    }

    /**
     * @test
     * @return void
     */
    public function catchExceptionWhenNoExtensionConfigurationIsGiven(): void
    {
        $this->assertInstanceOf(Event::class, BeforeEventListener::onBeforeSend($this->event));
    }
}

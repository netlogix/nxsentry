<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Tests\Integration;

use Netlogix\Nxsentry\Integration\RequestContext;
use Nimut\TestingFramework\Bootstrap\SystemEnvironmentBuilder;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Sentry\Event;
use TYPO3\CMS\Core\Http\ServerRequest;

use function Sentry\init;

class RequestContextTest extends UnitTestCase
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
        $requestContext = new RequestContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        $this->assertFalse($requestContext->appliesToEvent($this->event));
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInFrontendContext(): void
    {
        $requestContext = new RequestContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $this->assertTrue($requestContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInAjaxContext(): void
    {
        $requestContext = new RequestContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_AJAX);
        $this->assertTrue($requestContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInBackendContext(): void
    {
        $requestContext = new RequestContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $this->assertTrue($requestContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function addRequestDataToEvent(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        init([]);

        $requestContext = new RequestContext();
        $requestContext->addToEvent($this->event);

        $this->assertEquals($this->event->getRequest(), [
            'url' => 'https://www.example.com/?foo=bar',
            'method' => 'GET',
            'query_string' => 'foo=bar',
            'headers' => [
                'host' => [
                    0 => 'www.example.com'
                ]
            ],
        ]);
    }

}

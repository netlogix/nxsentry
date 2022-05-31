<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Tests\Integration;

use Jean85\PrettyVersions;
use Netlogix\Nxsentry\Integration\Typo3Context;
use Nimut\TestingFramework\Bootstrap\SystemEnvironmentBuilder;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Sentry\Event;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;

class Typo3ContextTest extends UnitTestCase
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
        $typo3Context = new Typo3Context();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        $this->assertTrue($typo3Context->appliesToEvent($this->event));
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInFrontendContext(): void
    {
        $typo3Context = new Typo3Context();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $this->assertTrue($typo3Context->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInAjaxContext(): void
    {
        $typo3Context = new Typo3Context();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_AJAX);
        $this->assertTrue($typo3Context->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInBackendContext(): void
    {
        $typo3Context = new Typo3Context();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $this->assertTrue($typo3Context->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function addTypo3DataAsTags(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $version = new Typo3Version();

        $typo3Context = new Typo3Context();
        $typo3Context->addToEvent($this->event);

        $this->assertEquals($this->event->getTags(), [
            'typo3_version' => $version->getVersion(),
            'application_type' => 'frontend',
            'application_context' => 'Testing',
            'application_version' => PrettyVersions::getRootPackageVersion()->getPrettyVersion(),
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function addUnknownApplicationTypeWhenNotSet(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'));

        $typo3Context = new Typo3Context();
        $typo3Context->addToEvent($this->event);

        $this->assertEquals($this->event->getTags()['application_type'], 'not-resolved');
    }

}

<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Tests\Integration;

use Netlogix\Nxsentry\Integration\UserContext;
use Nimut\TestingFramework\Bootstrap\SystemEnvironmentBuilder;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Sentry\Event;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class UserContextTest extends UnitTestCase
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
        $userContext = new UserContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
        $this->assertFalse($userContext->appliesToEvent($this->event));
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInFrontendContext(): void
    {
        $userContext = new UserContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $this->assertTrue($userContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInAjaxContext(): void
    {
        $userContext = new UserContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_AJAX);
        $this->assertTrue($userContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function appliesToEventInBackendContext(): void
    {
        $userContext = new UserContext();

        SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $this->assertTrue($userContext->appliesToEvent($this->event), 'Frontend context');
    }

    /**
     * @test
     * @return void
     */
    public function addUserDataToEvent(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $userContext = new UserContext();
        $userContext->addToEvent($this->event);

        $user = $this->event->getUser();

        $this->assertEquals($user->getIpAddress(), '192.168.1.1');
    }

    /**
     * @test
     * @return void
     */
    public function addFrontendUserDataToEvent(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $user = new FrontendUserAuthentication();
        $user->user['uid'] = 1;
        $user->user['username'] = 'sascha.nowak';
        $user->userGroups = [
            ['uid' => 1, 'title' => 'some-user-group']
        ];

        $userAspect = new UserAspect($user);
        $context = GeneralUtility::makeInstance(Context::class);

        $context->setAspect('frontend.user', $userAspect);

        $userContext = new UserContext();
        $userContext->addToEvent($this->event);

        $user = $this->event->getUser();

        $this->assertEquals($user->getId(), 1);
        $this->assertEquals($user->getUsername(), 'sascha.nowak');
        $this->assertEquals($user->getMetadata(), ['groups' => 'some-user-group']);
    }

    /**
     * @test
     * @return void
     */
    public function addBackendUserDataToEvent(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://www.example.com/?foo=bar'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $user = new BackendUserAuthentication();
        $user->user['uid'] = 1;
        $user->user['username'] = 'sascha.nowak';
        $user->userGroups = [
            ['uid' => 1, 'title' => 'some-backend-user-group']
        ];

        $userAspect = new UserAspect($user);
        $context = GeneralUtility::makeInstance(Context::class);

        $context->setAspect('backend.user', $userAspect);

        $userContext = new UserContext();
        $userContext->addToEvent($this->event);

        $user = $this->event->getUser();

        $this->assertEquals($user->getId(), 1);
        $this->assertEquals($user->getUsername(), 'sascha.nowak');
        $this->assertEquals($user->getMetadata(), ['groups' => 'some-backend-user-group']);
    }

}

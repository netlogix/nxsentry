<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry;

use Netlogix\Nxsentry\Integration\BeforeEventListener;
use Jean85\PrettyVersions;
use Sentry\Integration\EnvironmentIntegration;
use Sentry\Integration\FatalErrorListenerIntegration;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function Sentry\init;

final class Sentry
{
    /**
     * @var bool
     */
    private static $initialized = false;

    public static function initializeOnce(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        $options = [
            'dsn' => getenv('SENTRY_DSN'),
            'in_app_exclude' => [
                Environment::getConfigPath(),
                Environment::getVarPath(),
                Environment::getComposerRootPath() . '/vendor',
                Environment::getComposerRootPath() . '/Vendor',
                getenv('TYPO3_PATH_ROOT') . '/typo3',
                getenv('TYPO3_PATH_WEB'),
            ],
            'prefixes' => [
                getenv('TYPO3_PATH_APP'),
            ],
            'environment' =>  getenv('SENTRY_ENVIRONMENT') ?: ((string)Environment::getContext()->isProduction() ? 'production' : 'development'),
            'release' => getenv('SENTRY_RELEASE') ?: PrettyVersions::getRootPackageVersion()->getShortReference(),
            'default_integrations' => false,
            'integrations' => [
                new EnvironmentIntegration(),
                new FatalErrorListenerIntegration(),
            ],
            'before_send' => [BeforeEventListener::class, 'onBeforeSend'],
            'error_types' => E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED),
        ];

        try {
            $options = array_replace(
                $options,
                GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('nxsentry', 'options') ?? []
            );
        } catch (\Throwable $t) {
        } finally {
            init($options);
        }
    }
}

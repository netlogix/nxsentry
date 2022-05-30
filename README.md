[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Packagist](https://img.shields.io/packagist/v/netlogix/nxsentry.svg)](https://packagist.org/packages/netlogix/nxsentry)
[![Maintenance level: Love](https://img.shields.io/badge/maintenance-%E2%99%A1%E2%99%A1%E2%99%A1-ff69b4.svg)](https://websolutions.netlogix.de/)
[![stability-wip](https://img.shields.io/badge/stability-wip-lightgrey.svg)](hhttps://github.com/netlogix/nxsentry)
[![TYPO3 V10](https://img.shields.io/badge/TYPO3-10-orange.svg)](https://get.typo3.org/version/10)
[![TYPO3 V11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/11)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![GitHub CI status](https://github.com/netlogix/nxsentry/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/netlogix/nxsentry/actions)

# Sentry integration for TYPO3

This [TYPO3](https://typo3.org/) extension allows you to automate reporting of errors to [Sentry](https://www.sentry.io)

## Installation

The Sentry integration is installed as a composer package. For your existing project, simply include `netlogix/nxsentry`
into the dependencies of your TYPO3 distribution:

```bash
$ composer require netlogix/nxsentry
```

## Configuration

The new Sentry SDK 3.x has some environment variables which can be used, for example in a .env file:
```apacheconfig
SENTRY_DSN='http://public_key@your-sentry-server.com/project-id'
SENTRY_RELEASE='1.0.7'
SENTRY_ENVIRONMENT='Staging'
```

Add this to your `LocalConfiguration.php`
```php
return [
    'LOG' => [
        'writerConfiguration' => [
            \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
                'Netlogix\Nxsentry\Log\Writer\SentryBreadcrumbWriter' => [],
                'Netlogix\Nxsentry\Log\Writer\SentryWriter' => [],
            ],
        ],
    ],
];
```

Overwriting default options in the `LocalConfiguration.php`
```php
return [
    'EXTENSIONS' => [
        'nxsentry' => [
            'dsn' => 'http://public_key@your-sentry-server.com/project-id'
        ],
    ],
];
```

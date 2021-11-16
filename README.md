[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Packagist](https://img.shields.io/packagist/v/netlogix/nxsentry.svg)](https://packagist.org/packages/netlogix/nxsentry)
[![Maintenance level: Love](https://img.shields.io/badge/maintenance-%E2%99%A1%E2%99%A1%E2%99%A1-ff69b4.svg)](https://websolutions.netlogix.de/)

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
            'error' => [
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

<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Strategy;

use Http\Discovery\Strategy\DiscoveryStrategy;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Typo3ClassesStrategy implements DiscoveryStrategy
{
    private static $classes = [
        RequestFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\RequestFactory::class
        ],
        ResponseFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\ResponseFactory::class
        ],
        ServerRequestFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\ServerRequestFactory::class
        ],
        StreamFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\StreamFactory::class
        ],
        UploadedFileFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\UploadedFileFactory::class
        ],
        UriFactoryInterface::class => [
            \TYPO3\CMS\Core\Http\UriFactory::class
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function getCandidates($type)
    {
        $candidates = [];
        if (isset(self::$classes[$type])) {
            foreach (self::$classes[$type] as $class) {
                $candidates[] = ['class' => $class, 'condition' => [$class]];
            }
        }

        return $candidates;
    }
}

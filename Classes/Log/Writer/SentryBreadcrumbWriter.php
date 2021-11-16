<?php
declare(strict_types=1);
namespace Netlogix\Nxsentry\Log\Writer;

use Netlogix\Nxsentry\Sentry;
use Sentry\Breadcrumb;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;

use function Sentry\addBreadcrumb;

/**
 * Log writer that adds breadcrumbs for Sentry.
 */
class SentryBreadcrumbWriter extends AbstractWriter
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        Sentry::initializeOnce();
    }

    public function writeLog(LogRecord $record): WriterInterface
    {
        addBreadcrumb(
            Breadcrumb::fromArray(
                [
                    'level' => $this->getSeverityFromLevel($record->getLevel()),
                    'message' => $record->getMessage(),
                    'data' => $record->getData(),
                    'category' => $record->getComponent(),
                ]
            )
        );

        return $this;
    }

    /**
     * Translates the TYPO3 logging framework level into the Sentry severity.
     */
    private function getSeverityFromLevel(string $level): string
    {
        switch ($level) {
            case LogLevel::DEBUG:
                return Breadcrumb::LEVEL_DEBUG;
            case LogLevel::INFO:
            case LogLevel::NOTICE:
                return Breadcrumb::LEVEL_INFO;
            case LogLevel::WARNING:
                return Breadcrumb::LEVEL_WARNING;
            case LogLevel::ERROR:
                return Breadcrumb::LEVEL_ERROR;
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::EMERGENCY:
                return Breadcrumb::LEVEL_FATAL;
            default:
                return Breadcrumb::LEVEL_INFO;
        }
    }
}

<?php

declare(strict_types=1);

namespace Netlogix\Nxsentry\Log\Writer;

use Netlogix\Nxsentry\Sentry;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Severity;
use Sentry\State\Scope;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function Sentry\captureEvent;
use function Sentry\withScope;

/**
 * Log writer that writes the log records to Sentry.
 */
class SentryWriter extends AbstractWriter
{
    /**
     * @var FileWriter
     */
    private $fallbackWriter;

    public function __construct(array $options = [])
    {
        parent::__construct([]);
        Sentry::initializeOnce();
        if (!empty($options)) {
            $this->fallbackWriter = GeneralUtility::makeInstance(FileWriter::class, $options);
        }
    }

    public function writeLog(LogRecord $record): WriterInterface
    {
        withScope(function (Scope $scope) use ($record) {
            $event = Event::createEvent();

            $message = $record->getMessage();
            $recordData = $record->getData();
            $event->setMessage($message, $recordData, $this->interpolate($message, $recordData));
            $event->setLevel($this->getSeverityFromLevel($record->getLevel()));
            $exception = $recordData['exception'] ?? null;
            $fingerprint = $recordData['fingerprint'] ?? null;
            if ($exception instanceof \Throwable) {
                $hint = new EventHint();
                $hint->exception = $exception;
                unset($recordData['exception']);
                if (!$fingerprint && $exception->getCode() > 1000000000) {
                    // If we track an exception and the code appears to be a timestamp,
                    // we assume it to be unique enough to make it the fingerprint
                    // instead of letting the fingerprint being based on the stacktrace,
                    // but only if no fingerprint was set from the caller
                    $fingerprint = [
                        (string)$exception->getCode()
                    ];
                }
            }
            if ($fingerprint) {
                $scope->setFingerprint($fingerprint);
                unset($recordData['fingerprint']);
            }
            $scope->setExtra('typo3.component', $record->getComponent());
            $scope->setExtra('typo3.level', $record->getLevel());
            $scope->setExtra('typo3.request_id', $record->getRequestId());
            if (!empty($recordData['tags'])) {
                foreach ($recordData['tags'] as $key => $value) {
                    $scope->setTag((string)$key, $value);
                }
                unset($recordData['tags']);
            }
            if (!empty($recordData['extra'])) {
                foreach ($recordData['extra'] as $key => $value) {
                    $scope->setExtra((string)$key, $value);
                }
                unset($recordData['extra']);
            }
            $recordData = array_filter($recordData);
            foreach ($recordData as $key => $value) {
                $scope->setExtra((string)$key, $value);
            }
            try {
                captureEvent($event, $hint ?? null);
            } catch (\Throwable $e) {
                // Avoid hard failure in case connection to sentry failed
                if ($this->fallbackWriter) {
                    $this->fallbackWriter->writeLog(
                        new LogRecord(
                            'Sentry.Writer',
                            LogLevel::ERROR,
                            'Failed to write to Sentry',
                            ['exception' => $e],
                            $record->getRequestId()
                        )
                    );
                }
            }
            if ($this->fallbackWriter && (isset($e) || empty(getenv('SENTRY_DNS')))) {
                $this->fallbackWriter->writeLog($record);
            }
        });

        return $this;
    }

    /**
     * Translates the TYPO3 logging framework level into the Sentry severity.
     */
    private function getSeverityFromLevel(string $level): Severity
    {
        switch ($level) {
            case LogLevel::DEBUG:
                return Severity::debug();
            case LogLevel::INFO:
            case LogLevel::NOTICE:
                return Severity::info();
            case LogLevel::WARNING:
                return Severity::warning();
            case LogLevel::ERROR:
                return Severity::error();
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::EMERGENCY:
                return Severity::fatal();
            default:
                return Severity::info();
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate(string $message, array $context = []): string
    {
        // Build a replacement array with braces around the context keys.
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && !is_null($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string)$val;
            }
        }

        // Interpolate replacement values into the message and return.
        return strtr($message, $replace);
    }
}

<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\LogRecord;

class PrependAppName
{
    public function __invoke(Logger $logger): void
    {
        $appName = config('app.name', 'Laravel');

        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function (LogRecord $record) use ($appName): LogRecord {
                return $record->with(
                    message: "[{$appName}] {$record->message}"
                );
            });
        }
    }
}

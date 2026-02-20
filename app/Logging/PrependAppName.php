<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class PrependAppName
{
    public function __invoke(Logger $logger): void
    {
        $appName = config('app.name', 'Laravel');

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                format: "({$appName}) %channel%.%level_name%: %message% %context% %extra%",
                allowInlineLineBreaks: true,
                ignoreEmptyContextAndExtra: true,
            ));
        }
    }
}

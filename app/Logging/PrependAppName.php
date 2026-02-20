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
            // On impose un format où le nom d'app est AVANT le datetime
            $handler->setFormatter(new LineFormatter(
                format: "({$appName}) [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                dateFormat: 'Y-m-d H:i:s',
                allowInlineLineBreaks: true,
                ignoreEmptyContextAndExtra: true,
            ));
        }
    }
}

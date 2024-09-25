<?php

return [

    'ADMIN_EMAILS' => env('ADMIN_EMAILS', ''),
    'ADMIN_USERNAMES' => env('ADMIN_USERNAMES', ''),
    'FEEDBACK_MAIL' => env('FEEDBACK_MAIL', ''),
    'SITEID' => env('SITEID', ''),
    'SITEPATH' => env('SITEPATH', ''),
    'STOPREINDEXING' => env('STOPREINDEXING', 0),
    'start_date' => '2023-09-25',
    'POWERBI' => env('POWERBI', ''),
    'TRANSLATIONS' => env('TRANSLATIONS', false),

    'webt' => [
        'clientId' => env('WEBT_CLIENTID', false),
        'url' => env('WEBT_URL', false),
        'version' => env('WEBT_VERSION', 2),
    ]

];

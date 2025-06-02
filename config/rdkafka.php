<?php

return [
    'brokers' => env('KAFKA_BROKERS', false), // OVH Kafka server
    'security' => [
        'protocol' => env('KAFKA_SECURITY_PROTOCOL', 'SSL'), // Default to SSL
    ],
    'ssl' => [
        'key_location' => env('KAFKA_SSL_KEY_LOCATION', __DIR__ . '/../kafka-service.key'),
        'ca_location' => env('KAFKA_SSL_CA_LOCATION', __DIR__ . '/../kafka-service.cert'),
        'certificate_location' => env('KAFKA_SSL_CERTIFICATE_LOCATION', __DIR__ . '/../kafka-service.cert'),
        'enable_ssl_certificate_verification' => env('KAFKA_ENABLE_SSL_CERTIFICATE_VERIFICATION', 'false'), // Default to false
    ],
    'group_id' => env('KAFKA_GROUP_ID', 'php-group'), // Default group ID
    'auto_offset_reset' => env('KAFKA_AUTO_OFFSET_RESET', 'earliest'), // Default offset reset policy
    'topic' => env('KAFKA_TOPIC', 'transparency_statements'), // Default topic to use
];
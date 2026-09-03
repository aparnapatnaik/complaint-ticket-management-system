<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Aws\Sqs\SqsClient;

return [
    'client' => new SqsClient([
        'version' => 'latest',
        'region'  => 'ap-south-2',
    ]),

    'queue_url' => 'https://sqs.ap-south-2.amazonaws.com/958295071309/complaint-events',
];

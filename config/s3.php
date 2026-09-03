<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;

return [
    'client' => new S3Client([
        'version' => 'latest',
        'region'  => 'ap-south-2',
    ]),

    'bucket' => 'complaint-system-files-958295071309-2026',
];

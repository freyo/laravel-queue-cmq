<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`
 */
return [
    'driver'     => 'cmq',
    'secret_key' => env('CMQ_SECRET_KEY', 'your-secret-key'),
    'secret_id'  => env('CMQ_SECRET_ID', 'your-secret-id'),
    'host'       => env('CMQ_HOST', 'https://cmq-queue-region.api.qcloud.com'),
    'queue'      => env('CMQ_QUEUE', 'default'),
];
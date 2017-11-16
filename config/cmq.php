<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`
 */
return [

    'driver' => 'cmq',

    'secret_key' => env('CMQ_SECRET_KEY', 'your-secret-key'),
    'secret_id'  => env('CMQ_SECRET_ID', 'your-secret-id'),

    'options' => [
        'queue' => [
            'host' => env('CMQ_QUEUE_HOST', 'https://cmq-queue-region.api.qcloud.com'),
            'name' => env('CMQ_QUEUE', 'default'),
        ],
        'topic' => [
            'enable' => env('CMQ_TOPIC_ENABLE', false),
            'filter' => env('CMQ_TOPIC_FILTER', 'routing'), //routing or msgtag
            'host'   => env('CMQ_TOPIC_HOST', 'https://cmq-topic-region.api.qcloud.com'),
            'name'   => env('CMQ_TOPIC'),
        ],
    ],

];

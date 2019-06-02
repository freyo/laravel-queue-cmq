<?php

namespace Freyo\LaravelQueueCMQ\Queue\Connectors;

use Freyo\LaravelQueueCMQ\Queue\CMQQueue;
use Freyo\LaravelQueueCMQ\Queue\Driver\Account;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;

class CMQConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     * @throws \ReflectionException
     */
    public function connect(array $config)
    {
        $queue = new Account(
            Arr::get($config, 'options.queue.host'),
            $config['secret_id'],
            $config['secret_key']
        );

        $topic = new Account(
            Arr::get($config, 'options.topic.host'),
            $config['secret_id'],
            $config['secret_key']
        );

        return new CMQQueue($queue, $topic, $config);
    }
}

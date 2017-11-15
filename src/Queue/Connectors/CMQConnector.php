<?php

namespace Freyo\LaravelQueueCMQ\Queue\Connectors;

use Freyo\LaravelQueueCMQ\Queue\Driver\Account;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Events\WorkerStopping;
use Freyo\LaravelQueueCMQ\Queue\CMQQueue;

class CMQConnector implements ConnectorInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $account = new Account($config['host'], $config['secret_id'], $config['secret_key']);

        $this->dispatcher->listen(WorkerStopping::class, function () use (&$account) {
            unset($account);
        });

        return new CMQQueue($account, $config);
    }
}
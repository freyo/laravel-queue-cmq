<?php

namespace Freyo\LaravelQueueCMQ;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;

class LaravelQueueCMQServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cmq.php', 'queue.connections.cmq'
        );
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];

        $queue->addConnector('cmq', function () {
            return new CMQConnector($this->app['events']);
        });
    }
}
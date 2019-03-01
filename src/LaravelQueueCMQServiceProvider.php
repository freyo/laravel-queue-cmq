<?php

namespace Freyo\LaravelQueueCMQ;

use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class LaravelQueueCMQServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->configure('queue');
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/cmq.php', 'queue.connections.cmq'
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
            return new CMQConnector();
        });
    }
}

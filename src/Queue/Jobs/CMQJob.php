<?php

namespace Freyo\LaravelQueueCMQ\Queue\Jobs;

use Freyo\LaravelQueueCMQ\Queue\CMQQueue;
use Freyo\LaravelQueueCMQ\Queue\Driver\Message;
use Freyo\LaravelQueueCMQ\Queue\Driver\Queue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class CMQJob extends Job implements JobContract
{
    /**
     * @var \Freyo\LaravelQueueCMQ\Queue\CMQQueue
     */
    protected $connection;

    /**
     * @var \Freyo\LaravelQueueCMQ\Queue\Driver\Message
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Container\Container $container
     * @param \Freyo\LaravelQueueCMQ\Queue\CMQQueue $connection
     * @param \Freyo\LaravelQueueCMQ\Queue\Driver\Message $message
     * @param \Freyo\LaravelQueueCMQ\Queue\Driver\Queue $queue
     * @param string $connectionName
     */
    public function __construct(Container $container, CMQQueue $connection, Message $message, Queue $queue, $connectionName)
    {
        $this->container = $container;
        $this->connection = $connection;
        $this->message = $message;
        $this->queue = $queue->getQueueName();
        $this->connectionName = $connectionName;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->message->msgId;
    }

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->message->msgBody;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->message->dequeueCount;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        method_exists($this, 'resolveAndFire')
            ? $this->resolveAndFire($this->payload())
            : parent::fire();
    }

    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    public function payload()
    {
        if ($this->connection->isPlain()) {
            $job = $this->connection->getPlainJob();

            return [
                'displayName' => is_string($job) ? explode('@', $job)[0] : null,
                'job'         => $job,
                'maxTries'    => null,
                'timeout'     => null,
                'data'        => $this->getRawBody(),
            ];
        }

        return json_decode($this->getRawBody(), true);
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->connection->getQueue($this->getQueue())->delete_message($this->message->receiptHandle);
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
    }
}

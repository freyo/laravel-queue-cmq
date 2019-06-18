<?php

namespace Freyo\LaravelQueueCMQ\Queue;

use Freyo\LaravelQueueCMQ\Queue\Driver\Account;
use Freyo\LaravelQueueCMQ\Queue\Driver\CMQServerException;
use Freyo\LaravelQueueCMQ\Queue\Driver\Message;
use Freyo\LaravelQueueCMQ\Queue\Driver\Topic;
use Freyo\LaravelQueueCMQ\Queue\Jobs\CMQJob;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
use Illuminate\Support\Arr;

class CMQQueue extends Queue implements QueueContract
{
    const CMQ_QUEUE_NO_MESSAGE_CODE = 7000;

    const CMQ_TOPIC_TAG_FILTER_NAME = 'msgtag';
    const CMQ_TOPIC_ROUTING_FILTER_NAME = 'routing';

    /**
     * @var array
     */
    protected $queueOptions;

    /**
     * @var array
     */
    protected $topicOptions;

    /**
     * @var \Freyo\LaravelQueueCMQ\Queue\Driver\Account
     */
    private $queueAccount;

    /**
     * @var \Freyo\LaravelQueueCMQ\Queue\Driver\Account
     */
    private $topicAccount;

    /**
     * @var array
     */
    protected $plainOptions;

    /**
     * @var \ReflectionMethod
     */
    private $createPayload;

    /**
     * CMQQueue constructor.
     *
     * @param \Freyo\LaravelQueueCMQ\Queue\Driver\Account $queueAccount
     * @param \Freyo\LaravelQueueCMQ\Queue\Driver\Account $topicAccount
     * @param array                                       $config
     *
     * @throws \ReflectionException
     */
    public function __construct(Account $queueAccount, Account $topicAccount, array $config)
    {
        $this->queueAccount = $queueAccount;
        $this->topicAccount = $topicAccount;

        $this->queueOptions = $config['options']['queue'];
        $this->topicOptions = $config['options']['topic'];

        $this->plainOptions = Arr::get($config, 'plain', []);

        $this->createPayload = new \ReflectionMethod($this, 'createPayload');
    }

    /**
     * @return bool
     */
    public function isPlain()
    {
        return (bool) Arr::get($this->plainOptions, 'enable');
    }

    /**
     * @return string
     */
    public function getPlainJob()
    {
        return Arr::get($this->plainOptions, 'job');
    }

    /**
     * Get the size of the queue.
     *
     * @param string $queue
     *
     * @return int
     */
    public function size($queue = null)
    {
        $attributes = $this->getQueue($queue)->get_attributes();

        return (int) $attributes->activeMsgNum;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string|object $job
     * @param mixed         $data
     * @param string        $queue
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        if ($this->isPlain()) {
            return $this->pushRaw($job->getPayload(), $queue);
        }

        $payload = $this->createPayload->getNumberOfParameters() === 3
            ? $this->createPayload($job, $queue, $data) // version >= 5.7
            : $this->createPayload($job, $data);

        return $this->pushRaw($payload, $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array  $options
     *
     * @throws \Freyo\LaravelQueueCMQ\Queue\Driver\CMQServerNetworkException
     * @throws \Freyo\LaravelQueueCMQ\Queue\Driver\CMQServerException
     * @throws \Exception
     *
     * @return \Freyo\LaravelQueueCMQ\Queue\Driver\Message|array
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $message = new Message($payload);

        $driver = $this->parseQueue($queue);

        if ($driver instanceof Topic) {
            switch ($this->topicOptions['filter']) {
                case self::CMQ_TOPIC_TAG_FILTER_NAME:
                    return retry(Arr::get($this->topicOptions, 'retries', 3),
                        function () use ($driver, $message, $queue) {
                            return $driver->publish_message($message->msgBody, explode(',', $queue), null);
                        });
                case self::CMQ_TOPIC_ROUTING_FILTER_NAME:
                    return retry(Arr::get($this->topicOptions, 'retries', 3),
                        function () use ($driver, $message, $queue) {
                            $driver->publish_message($message->msgBody, [], $queue);
                        });
                default:
                    throw new \InvalidArgumentException(
                        'Invalid CMQ topic filter: '.$this->topicOptions['filter']
                    );
            }
        }

        return retry(Arr::get($this->queueOptions, 'retries', 3), function () use ($driver, $message, $options) {
            return $driver->send_message($message, Arr::get($options, 'delay', 0));
        });
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string|object                        $job
     * @param mixed                                $data
     * @param string                               $queue
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $delay = method_exists($this, 'getSeconds')
            ? $this->getSeconds($delay)
            : $this->secondsUntil($delay);

        if ($this->isPlain()) {
            return $this->pushRaw($job->getPayload(), $queue, ['delay' => $delay]);
        }

        $payload = $this->createPayload->getNumberOfParameters() === 3
            ? $this->createPayload($job, $queue, $data) // version >= 5.7
            : $this->createPayload($job, $data);

        return $this->pushRaw($payload, $queue, ['delay' => $delay]);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     *
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        try {
            $queue = $this->getQueue($queue);
            $message = $queue->receive_message($this->queueOptions['polling_wait_seconds']);
        } catch (CMQServerException $e) {
            if (self::CMQ_QUEUE_NO_MESSAGE_CODE === (int) $e->getCode()) { // ignore no message
                return;
            }

            throw $e;
        }

        return new CMQJob($this->container, $this, $message, $queue, $this->connectionName);
    }

    /**
     * Get the queue.
     *
     * @param string $queue
     *
     * @return Driver\Queue
     */
    public function getQueue($queue = null)
    {
        return $this->queueAccount->get_queue($queue ?: $this->queueOptions['name']);
    }

    /**
     * Get the topic.
     *
     * @param string $topic
     *
     * @return Driver\Topic
     */
    public function getTopic($topic = null)
    {
        return $this->topicAccount->get_topic($topic ?: $this->topicOptions['name']);
    }

    /**
     * Parse name to topic or queue.
     *
     * @param string $queue
     *
     * @return Driver\Queue|Driver\Topic
     */
    public function parseQueue($queue = null)
    {
        if ($this->topicOptions['enable']) {
            return $this->getTopic($this->topicOptions['name'] ?: $queue);
        }

        return $this->getQueue($queue ?: $this->queueOptions['name']);
    }
}

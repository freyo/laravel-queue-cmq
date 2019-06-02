<?php

namespace Freyo\LaravelQueueCMQ\Tests;

use Freyo\LaravelQueueCMQ\Queue\CMQQueue;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use Freyo\LaravelQueueCMQ\Queue\Driver\Message;
use Freyo\LaravelQueueCMQ\Queue\Driver\Queue;
use Freyo\LaravelQueueCMQ\Queue\Driver\Topic;
use Freyo\LaravelQueueCMQ\Queue\Jobs\CMQJob;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class CMQQueueTest extends TestCase
{
    public function provider()
    {
        $config = [

            'driver' => 'cmq',

            'secret_key' => env('CMQ_SECRET_KEY', 'your-secret-key'),
            'secret_id'  => env('CMQ_SECRET_ID', 'your-secret-id'),

            'queue' => env('CMQ_QUEUE', 'default'),

            'options' => [
                'queue' => [
                    'host'                 => env('CMQ_QUEUE_HOST', 'https://cmq-queue-region.api.qcloud.com'),
                    'name'                 => env('CMQ_QUEUE', 'default'),
                    'polling_wait_seconds' => env('CMQ_QUEUE_POLLING_WAIT_SECONDS', 0), //0-30seconds
                    'retries'              => env('CMQ_QUEUE_RETRIES', 1),
                ],
                'topic' => [
                    'enable'  => env('CMQ_TOPIC_ENABLE', false),
                    'filter'  => env('CMQ_TOPIC_FILTER', 'routing'), //routing or msgtag
                    'host'    => env('CMQ_TOPIC_HOST', 'https://cmq-topic-region.api.qcloud.com'),
                    'name'    => env('CMQ_TOPIC'),
                    'retries' => env('CMQ_TOPIC_RETRIES', 1),
                ],
            ],

            'plain' => [
                'enable' => env('CMQ_PLAIN_ENABLE', false),
                'job'    => env('CMQ_PLAIN_JOB', 'App\Jobs\CMQPlainJob@handle'),
            ],

        ];

        $connector = new CMQConnector();

        $queue = $connector->connect($config);

        return [
            [$queue, $config],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testIsPlain(CMQQueue $queue, $config)
    {
        $this->assertSame($config['plain']['enable'], $queue->isPlain());
    }

    /**
     * @dataProvider provider
     */
    public function testGetPlainJob(CMQQueue $queue, $config)
    {
        $this->assertSame($config['plain']['job'], $queue->getPlainJob());
    }

    /**
     * @dataProvider provider
     */
    public function testSize(CMQQueue $queue, $config)
    {
        $this->assertGreaterThanOrEqual(0, $queue->size());
    }

    /**
     * @dataProvider provider
     */
    public function testPush(CMQQueue $queue, $config)
    {
        $this->assertInstanceOf(Message::class, $queue->push('App\Jobs\CMQJob@handle'));
    }

    /**
     * @dataProvider provider
     */
    public function testPushRaw(CMQQueue $queue, $config)
    {
        $this->assertInstanceOf(Message::class, $queue->pushRaw('App\Jobs\CMQJob@handle'));
    }

    /**
     * @dataProvider provider
     */
    public function testLater(CMQQueue $queue, $config)
    {
        $queue->later(0, 'App\Jobs\CMQJob@handle');
    }

    /**
     * @dataProvider provider
     */
    public function testPop(CMQQueue $queue, $config)
    {
        $queue->setContainer(new Container());

        $this->assertInstanceOf(CMQJob::class, $queue->pop());
    }

    /**
     * @dataProvider provider
     */
    public function testGetQueue(CMQQueue $queue, $config)
    {
        $this->assertInstanceOf(Queue::class, $queue->getQueue());
    }

    /**
     * @dataProvider provider
     */
    public function testGetTopic(CMQQueue $queue, $config)
    {
        $this->assertInstanceOf(Topic::class, $queue->getTopic());
    }

    /**
     * @dataProvider provider
     */
    public function testParseQueue(CMQQueue $queue, $config)
    {
        $this->assertInstanceOf(
            $config['options']['topic']['enable']
                ? Topic::class
                : Queue::class,
            $queue->parseQueue()
        );
    }
}

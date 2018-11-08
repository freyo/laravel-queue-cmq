<div>
  <p align="center">
    <image src="https://mc.qcloudimg.com/static/img/7fc29d4e11d2ae302cf7f77d16c78f42/CMQ.svg" width="220" height="220">
  </p>
  <p align="center">
    <a href="LICENSE">
      <image src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License">
    </a>
    <a href="https://travis-ci.org/freyo/laravel-queue-cmq">
      <image src="https://img.shields.io/travis/freyo/laravel-queue-cmq/master.svg?style=flat-square" alt="Build Status">
    </a>
    <a href="https://scrutinizer-ci.com/g/freyo/laravel-queue-cmq">
      <image src="https://img.shields.io/scrutinizer/coverage/g/freyo/laravel-queue-cmq.svg?style=flat-square" alt="Coverage Status">
    </a>
    <a href="https://scrutinizer-ci.com/g/freyo/laravel-queue-cmq">
      <image src="https://img.shields.io/scrutinizer/g/freyo/laravel-queue-cmq.svg?style=flat-square" alt="Quality Score">
    </a>
    <a href="https://packagist.org/packages/freyo/laravel-queue-cmq">
      <image src="https://img.shields.io/packagist/v/freyo/laravel-queue-cmq.svg?style=flat-square" alt="Packagist Version">
    </a>
    <a href="https://packagist.org/packages/freyo/laravel-queue-cmq">
      <image src="https://img.shields.io/packagist/dt/freyo/laravel-queue-cmq.svg?style=flat-square" alt="Total Downloads">
    </a>
  </p>
  <p align="center">Tencent Cloud Message Queue driver for Laravel Queue</p>
  <p align="center">腾讯云消息队列 CMQ</p>
  <p align="center">安全可靠、扩展性高且业务可用性强的高性能分布式消息队列服务</p>
</div>
      
## Installation

  ```shell
  composer require freyo/laravel-queue-cmq
  ```

## Configure

**Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.**

1. `config/app.php`:

  ```php
  'providers' => [
    // ...
    Freyo\LaravelQueueCMQ\LaravelQueueCMQServiceProvider::class,
  ]
  ```
  
2. `.env`:

  ```
  QUEUE_DRIVER=cmq
  
  CMQ_SECRET_KEY=
  CMQ_SECRET_ID=
  
  CMQ_QUEUE_HOST=https://cmq-queue-{region}.api.qcloud.com
  CMQ_QUEUE=queue_name #default queue name
  CMQ_QUEUE_POLLING_WAIT_SECONDS=0
  
  CMQ_TOPIC_ENABLE=false # set to true to use topic
  CMQ_TOPIC_FILTER=routing # or msgtag
  CMQ_TOPIC_HOST=https://cmq-topic-{region}.api.qcloud.com
  CMQ_TOPIC=topic_name
  ```
  
#### Tips
  
- Region should be replaced with a specific region: gz (Guangzhou), sh (Shanghai), or bj (Beijing).
  
- Domain for public network API request: cmq-queue-region.api.qcloud.com / cmq-topic-region.api.qcloud.com
  
- Domain for private network API request: cmq-queue-region.api.tencentyun.com / cmq-topic-region.api.tencentyun.com
  
## Usage

Once you completed the configuration you can use Laravel Queue API. If you used other queue drivers you do not need to change anything else. If you do not know how to use Queue API, please refer to the official Laravel documentation: http://laravel.com/docs/queues

### Example

#### Dispatch Jobs

The default connection name is `cmq`

  ```php
  //use queue only
  Job::dispatch()->onConnection('connection-name')->onQueue('queue-name');
  // or dispatch((new Job())->onConnection('connection-name')->onQueue('queue-name'))
  
  //use topic and tag filter
  Job::dispatch()->onConnection('connection-name')->onQueue('tag1,tag2,tag3');
  // or dispatch((new Job())->onConnection('connection-name')->onQueue('tag1,tag2,tag3'))
  
  //use topic and routing filter
  Job::dispatch()->onConnection('connection-name')->onQueue('routing-key');
  // or dispatch((new Job())->onConnection('connection-name')->onQueue('routing-key'))
  ```

#### Multiple Queues

Configure `config/queue.php`

```php
'connections' => [
    //...
    'new-connection-name' => [
        'driver' => 'cmq',
        'secret_key' => 'your-secret-key',
        'secret_id'  => 'your-secret-id',
        'queue' => 'your-queue-name',
        'options' => [
            'queue' => [
                'host'                 => 'https://cmq-queue-region.api.qcloud.com',
                'name'                 => 'your-queue-name',
                'polling_wait_seconds' => 0, // 0-30 seconds
            ],
            'topic' => [
                'enable' => false,
                'filter' => 'routing', // routing or msgtag
                'host'   => 'https://cmq-topic-region.api.qcloud.com',
                'name'   => '',
            ],
        ],
        'plain' => [
            'enable' => false,
            'job' => 'App\Jobs\CMQPlainJob@handle',
        ],
    ];
    //...
];
```

#### Process Jobs

```bash
php artisan queue:work {connection-name} --queue={queue-name}
```

#### Plain Mode (Experimental)

```
CMQ_PLAIN_ENABLE=true
CMQ_PLAIN_JOB=App\Jobs\CMQPlainJobHandler@handle
```

```php
<?php

namespace App\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Jobs\Job;

class CMQPlainJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }
    
    /**
     * Get the plain payload of the job.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
```

```php
<?php

namespace App\Jobs;

use Illuminate\Queue\Jobs\Job;

class CMQPlainJobHandler
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Job $job, $payload)
    {
        if (! $job->isDeletedOrReleased()) {
            $job->delete();
        }
        
        var_dump($payload);
    }
}
```

## References

- [Product Documentation](https://cloud.tencent.com/document/product/406?lang=en)
- [产品文档](https://cloud.tencent.com/document/product/406?lang=cn)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

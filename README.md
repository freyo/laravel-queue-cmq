<div>
  <p align="center">
    <image src="https://imgcache.qq.com/open_proj/proj_qcloud_v2/international/doc/css/img/icon/icon-zzj.svg" width="150" height="150">
  </p>
  <p align="center">Tencent Cloud Message Queue Driver for Laravel Queue</p>
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
  <p align="center">
    <a href="https://app.fossa.io/projects/git%2Bgithub.com%2Ffreyo%2Flaravel-queue-cmq?ref=badge_small">
      <img src="https://app.fossa.io/api/projects/git%2Bgithub.com%2Ffreyo%2Flaravel-queue-cmq.svg?type=small" alt="FOSSA Status">
    </a>
  </p>
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

#### Plain Mode

Configure `.env`

```
CMQ_PLAIN_ENABLE=true
CMQ_PLAIN_JOB=App\Jobs\CMQPlainJobHandler@handle
```

Create a job implements `PlainPayload` interface. 

The method `getPayload` must return a sting value.

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Freyo\LaravelQueueCMQ\Queue\Contracts\PlainPayload;

class CMQPlainJob implements ShouldQueue, PlainPayload
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

Create a plain job handler

```php
<?php

namespace App\Jobs;

use Illuminate\Queue\Jobs\Job;

class CMQPlainJobHandler
{
    /**
     * Execute the job.
     * 
     * @param \Illuminate\Queue\Jobs\Job $job
     * @param string $payload
     * 
     * @return void
     */
    public function handle(Job $job, $payload)
    {
        // processing your payload...
        var_dump($payload);
        
        // release back to the queue manually when failed.
        // $job->release();
        
        // delete message when processed.
        if (! $job->isDeletedOrReleased()) {
            $job->delete();
        }        
    }
}
```

## References

- [Product Documentation](https://intl.cloud.tencent.com/document/product/406)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Ffreyo%2Flaravel-queue-cmq.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Ffreyo%2Flaravel-queue-cmq?ref=badge_large)

# CMQ Queue driver for Laravel

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/freyo/laravel-queue-cmq.svg?style=flat-square)](https://packagist.org/packages/freyo/laravel-queue-cmq)
[![Total Downloads](https://img.shields.io/packagist/dt/freyo/laravel-queue-cmq.svg?style=flat-square)](https://packagist.org/packages/freyo/laravel-queue-cmq)

![image](https://mc.qcloudimg.com/static/img/7fc29d4e11d2ae302cf7f77d16c78f42/CMQ.svg)

Tencent Qcloud CMQ driver for Laravel Queue

腾讯云 CMQ 消息队列

## Installation

  ```shell
  composer require freyo/laravel-queue-cmq
  ```

## Configure

1. `config/app.php`:

  ```php
  'providers' => [
    // ...
    Freyo\LaravelQueueCMQ\LaravelQueueCMQServiceProvider::class,
  ]
  ```
  
2. `.env`:

  ```php
  CMQ_SECRET_KEY
  CMQ_SECRET_ID
  
  CMQ_QUEUE_HOST
  CMQ_QUEUE
  
  CMQ_TOPIC_ENABLE
  CMQ_TOPIC_FILTER
  CMQ_TOPIC_HOST
  CMQ_TOPIC
  ```
  
## Document

- [Overview](https://cloud.tencent.com/document/product/406?lang=en)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

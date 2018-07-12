<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class SubscriptionMeta
{
    // default NotifyStrategy      BACKOFF_RETRY
    // default NotifyContentFormat JSON

    /* 订阅属性
     @note: 可修改
     :: Endpoint            推送消息地址
     :: Protocal            协议
     :: FilterTag           订阅 标签
     :: NotifyStrategy      重试策略
     :: NotifyContentFormat 推送消息格式
     */
    public $Endpoint;
    public $Protocol;
    public $FilterTag;
    public $NotifyStrategy;
    public $NotifyContentFormat;

    public function __construct()
    {
        $this->Endpoint = '';
        $this->Protocol = '';
        $this->FilterTag = [];
        $this->NotifyStrategy = 'BACKOFF_RETRY';
        $this->NotifyContentFormat = 'JSON';
        $this->bindindKey = [];
    }

    public function __toString()
    {
        $info = [
            'endpoint'            => $this->Endpoint,
            'protocol'            => $this->Protocol,
            'filterTag'           => $this->FilterTag,
            'notifyStrategy'      => $this->NotifyStrategy,
            'notifyContentFormat' => $this->NotifyContentFormat,
            'bindingKey'          => $this->bindindKey,
        ];

        return json_encode($info);
    }
}

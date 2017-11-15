<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class TopicMeta
{

    // default maxMsgSize  65536
    // default msgRetentionSeconds 86400, one day

    /* 主题属性
    @note: 可修改
    :: maxMsgSize          消息最大值

    @note: 不可修改
    :: msgRetentionSeconds 消息最长保存时间，默认为 一天
    :: createTime          创建时间
    :: lastModifyTime      上次修改时间
    */
    public $maxMsgSize;
    public $msgRetentionSeconds;
    public $createTime;
    public $lastModifyTime;

    public function __construct()
    {
        $this->maxMsgSize          = 65536;
        $this->msgRetentionSeconds = 86400;
        $this->createTime          = 0;
        $this->lastModifyTime      = 0;
        $this->filterType          = 1;
    }

    public function __toString()
    {
        $info = array(
            "maxMsgSize"          => $this->maxMsgSize,
            "msgRetentionSeconds" => $this->msgRetentionSeconds,
            "createTime"          => $this->createTime,
            "lastModifyTime"      => $this->lastModifyTime,
            "filterType"          => $this->filterType
        );
        return json_encode($info);
    }
}
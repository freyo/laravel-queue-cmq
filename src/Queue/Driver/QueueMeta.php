<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class QueueMeta
{
    public $queueName;
    public $maxMsgHeapNum;
    public $pollingWaitSeconds;
    public $visibilityTimeout;
    public $maxMsgSize;
    public $msgRetentionSeconds;
    public $createTime;
    public $lastModifyTime;
    public $activeMsgNum;
    public $inactiveMsgNum;
    public $rewindSeconds;
    public $rewindmsgNum;
    public $minMsgTime;
    public $delayMsgNum;

    /* 队列属性
        @note: 设置属性
        :: maxMsgHeapNum: 最大堆积消息数
        :: pollingWaitSeconds: receive message时，长轮询时间，单位：秒
        :: visibilityTimeout: 消息可见性超时, 单位：秒
        :: maxMsgSize: 消息最大长度, 单位：Byte
		:: msgRetentionSeconds: 消息保留周期，单位：秒
		:: rewindSeconds ： 最大回溯时间， 单位：秒

        @note: 非设置属性
        :: activeMsgNum: 可消费消息数，近似值
        :: inactiveMsgNum: 正在被消费的消息数，近似值
        :: createTime: queue创建时间，单位：秒
        :: lastModifyTime: 修改queue属性的最近时间，单位：秒
		:: queue_name: 队列名称
		:: rewindmsgNum:已删除，但是任然在回溯保留时间内的消息数量
		:: minMsgTime: 消息最小未消费时间，单位为秒
		:: delayMsgNum:延时消息数量
    */
    public function __construct()
    {
        $this->queueName           = "";
        $this->maxMsgHeapNum       = -1;
        $this->pollingWaitSeconds  = 0;
        $this->visibilityTimeout   = 30;
        $this->maxMsgSize          = 65536;
        $this->msgRetentionSeconds = 345600;
        $this->createTime          = -1;
        $this->lastModifyTime      = -1;
        $this->activeMsgNum        = -1;
        $this->inactiveMsgNum      = -1;
        $this->rewindSeconds       = 0;
        $this->rewindmsgNum        = 0;
        $this->minMsgTime          = 0;
        $this->delayMsgNum         = 0;
    }

    public function __toString()
    {
        $info = array("visibilityTimeout"   => $this->visibilityTimeout,
                      "maxMsgHeapNum"       => $this->maxMsgHeapNum,
                      "maxMsgSize"          => $this->maxMsgSize,
                      "msgRetentionSeconds" => $this->msgRetentionSeconds,
                      "pollingWaitSeconds"  => $this->pollingWaitSeconds,
                      "activeMsgNum"        => $this->activeMsgNum,
                      "inactiveMsgNum"      => $this->inactiveMsgNum,
                      "createTime"          => date("Y-m-d H:i:s", $this->createTime),
                      "lastModifyTime"      => date("Y-m-d H:i:s", $this->lastModifyTime),
                      "QueueName"           => $this->queueName,
                      "rewindSeconds"       => $this->rewindSeconds,
                      "rewindmsgNum"        => $this->rewindmsgNum,
                      "minMsgTime"          => $this->minMsgTime,
                      "delayMsgNum"         => $this->delayMsgNum);
        return json_encode($info);
    }
}

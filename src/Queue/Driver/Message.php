<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;


class Message
{
    public $msgBody;
    public $msgId;
    public $enqueueTime;
    public $receiptHandle;

    /* 消息属性

        @note: send_message 指定属性
        :: msgBody         消息体

        @note: send_message 返回属性
        :: msgId           消息编号

        @note: receive_message 返回属性，除基本属性外
        :: receiptHandle       下次删除或修改消息的临时句柄
        :: enqueueTime         消息入队时间
        :: nextVisibleTime     下次可被再次消费的时间
        :: dequeueCount        总共被消费的次数
        :: firstDequeueTime    第一次被消费的时间
    */
    public function __construct($message_body = "")
    {
        $this->msgBody          = $message_body;
        $this->msgId            = "";
        $this->enqueueTime      = -1;
        $this->receiptHandle    = "";
        $this->nextVisibleTime  = -1;
        $this->dequeueCount     = -1;
        $this->firstDequeueTime = -1;
    }

    public function __toString()
    {
        $info = array("msgBody"          => $this->msgBody,
                      "msgId"            => $this->msgId,
                      "enqueueTime"      => date("Y-m-d H:i:s", $this->enqueueTime),
                      "nextVisibleTime"  => date("Y-m-d H:i:s", $this->nextVisibleTime),
                      "firstDequeueTime" => date("Y-m-d H:i:s", $this->firstDequeueTime),
                      "dequeueCount"     => $this->dequeueCount,
                      "receiptHandle"    => $this->receiptHandle);
        return json_encode($info);
    }
}


<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class Queue
{
    private $queue_name;
    private $cmq_client;
    private $encoding;

    public function __construct($queue_name, CMQClient $cmq_client, $encoding = false)
    {
        $this->queue_name = $queue_name;
        $this->cmq_client = $cmq_client;
        $this->encoding   = $encoding;
    }

    /* 设置是否对消息体进行base64编码

        @type encoding: bool
        @param encoding: 是否对消息体进行base64编码
    */
    public function set_encoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /* 创建队列

        @type queue_meta: QueueMeta object
        @param queue_meta: QueueMeta对象，设置队列的属性
    */
    public function create($queue_meta)
    {
        $params = array(
            'queueName'           => $this->queue_name,
            'pollingWaitSeconds'  => $queue_meta->pollingWaitSeconds,
            'visibilityTimeout'   => $queue_meta->visibilityTimeout,
            'maxMsgSize'          => $queue_meta->maxMsgSize,
            'msgRetentionSeconds' => $queue_meta->msgRetentionSeconds,
            'rewindSeconds'       => $queue_meta->rewindSeconds,
        );
        if ($queue_meta->maxMsgHeapNum > 0) {
            $params['maxMsgHeapNum'] = $queue_meta->maxMsgHeapNum;
        }
        $this->cmq_client->create_queue($params);
    }

    /* 获取队列属性

        @rtype: QueueMeta object
        @return 队列的属性
    */
    public function get_attributes()
    {
        $params                = array(
            'queueName' => $this->queue_name
        );
        $resp                  = $this->cmq_client->get_queue_attributes($params);
        $queue_meta            = new QueueMeta();
        $queue_meta->queueName = $this->queue_name;
        $this->__resp2meta__($queue_meta, $resp);
        return $queue_meta;
    }

    /* 设置队列属性

        @type queue_meta: QueueMeta object
        @param queue_meta: QueueMeta对象，设置队列的属性
    */

    protected function __resp2meta__($queue_meta, $resp)
    {
        if (isset($resp['queueName'])) {
            $queue_meta->queueName = $resp['queueName'];
        }
        if (isset($resp['maxMsgHeapNum'])) {
            $queue_meta->maxMsgHeapNum = $resp['maxMsgHeapNum'];
        }
        if (isset($resp['pollingWaitSeconds'])) {
            $queue_meta->pollingWaitSeconds = $resp['pollingWaitSeconds'];
        }
        if (isset($resp['visibilityTimeout'])) {
            $queue_meta->visibilityTimeout = $resp['visibilityTimeout'];
        }
        if (isset($resp['maxMsgSize'])) {
            $queue_meta->maxMsgSize = $resp['maxMsgSize'];
        }
        if (isset($resp['msgRetentionSeconds'])) {
            $queue_meta->msgRetentionSeconds = $resp['msgRetentionSeconds'];
        }
        if (isset($resp['createTime'])) {
            $queue_meta->createTime = $resp['createTime'];
        }
        if (isset($resp['lastModifyTime'])) {
            $queue_meta->lastModifyTime = $resp['lastModifyTime'];
        }
        if (isset($resp['activeMsgNum'])) {
            $queue_meta->activeMsgNum = $resp['activeMsgNum'];
        }
        if (isset($resp['rewindSeconds'])) {
            $queue_meta->rewindSeconds = $resp['rewindSeconds'];
        }
        if (isset($resp['inactiveMsgNum'])) {
            $queue_meta->inactiveMsgNum = $resp['inactiveMsgNum'];
        }
        if (isset($resp['rewindmsgNum'])) {
            $queue_meta->rewindmsgNum = $resp['rewindmsgNum'];
        }
        if (isset($resp['minMsgTime'])) {
            $queue_meta->minMsgTime = $resp['minMsgTime'];
        }
        if (isset($resp['delayMsgNum'])) {
            $queue_meta->delayMsgNum = $resp['delayMsgNum'];
        }

    }

    public function set_attributes($queue_meta)
    {
        $params = array(
            'queueName'           => $this->queue_name,
            'pollingWaitSeconds'  => $queue_meta->pollingWaitSeconds,
            'visibilityTimeout'   => $queue_meta->visibilityTimeout,
            'maxMsgSize'          => $queue_meta->maxMsgSize,
            'msgRetentionSeconds' => $queue_meta->msgRetentionSeconds,
            'rewindSeconds'       => $queue_meta->rewindSeconds
        );
        if ($queue_meta->maxMsgHeapNum > 0) {
            $params['maxMsgHeapNum'] = $queue_meta->maxMsgHeapNum;
        }

        $this->cmq_client->set_queue_attributes($params);
    }

    /* 删除队列

    */

    public function rewindQueue($backTrackingTime)
    {
        $params = array(
            'queueName'        => $this->queue_name,
            'startConsumeTime' => $backTrackingTime
        );
        $this->cmq_client->rewindQueue($params);
    }

    /* 发送消息

        @type message: Message object
        @param message: 发送的Message object

        @rtype: Message object
        @return 消息发送成功的返回属性，包含MessageId

    */

    public function delete()
    {
        $params = array('queueName' => $this->queue_name);
        $this->cmq_client->delete_queue($params);
    }

    /* 批量发送消息

       @type messages: list of Message object
       @param messages: 发送的Message object list

       @rtype: list of Message object
       @return 多条消息发送成功的返回属性，包含MessageId
    */

    public function send_message($message, $delayTime = 0)
    {
        if ($this->encoding) {
            $msgBody = base64_encode($message->msgBody);
        } else {
            $msgBody = $message->msgBody;
        }
        $params        = array(
            'queueName'    => $this->queue_name,
            'msgBody'      => $msgBody,
            'delaySeconds' => $delayTime
        );
        $msgId         = $this->cmq_client->send_message($params);
        $retmsg        = new Message();
        $retmsg->msgId = $msgId;
        return $retmsg;
    }

    /* 消费消息

        @type polling_wait_seconds: int
        @param polling_wait_seconds: 本次请求的长轮询时间，单位：秒

        @rtype: Message object
        @return Message object中包含基本属性、临时句柄
    */

    public function batch_send_message($messages, $delayTime = 0)
    {
        $params = array(
            'queueName'    => $this->queue_name,
            'delaySeconds' => $delayTime
        );
        $n      = 1;
        foreach ($messages as $message) {
            $key = 'msgBody.' . $n;
            if ($this->encoding) {
                $params[$key] = base64_encode($message->msgBody);
            } else {
                $params[$key] = $message->msgBody;
            }
            $n += 1;
        }
        $msgList        = $this->cmq_client->batch_send_message($params);
        $retMessageList = array();
        foreach ($msgList as $msg) {
            $retmsg            = new Message();
            $retmsg->msgId     = $msg['msgId'];
            $retMessageList [] = $retmsg;
        }
        return $retMessageList;
    }

    /* 批量消费消息

        @type num_of_msg: int
        @param num_of_msg: 本次请求最多获取的消息条数

        @type polling_wait_seconds: int
        @param polling_wait_seconds: 本次请求的长轮询时间，单位：秒

        @rtype: list of Message object
        @return 多条消息的属性，包含消息的基本属性、临时句柄
    */

    public function receive_message($polling_wait_seconds = NULL)
    {

        $params = array('queueName' => $this->queue_name);
        if ($polling_wait_seconds != NULL) {
            $params['UserpollingWaitSeconds'] = $polling_wait_seconds;
            $params['pollingWaitSeconds']     = $polling_wait_seconds;
        } else {
            $params['UserpollingWaitSeconds'] = 30;
        }
        $resp = $this->cmq_client->receive_message($params);
        $msg  = new Message();
        if ($this->encoding) {
            $msg->msgBody = base64_decode($resp['msgBody']);
        } else {
            $msg->msgBody = $resp['msgBody'];
        }
        $msg->msgId            = $resp['msgId'];
        $msg->receiptHandle    = $resp['receiptHandle'];
        $msg->enqueueTime      = $resp['enqueueTime'];
        $msg->nextVisibleTime  = $resp['nextVisibleTime'];
        $msg->dequeueCount     = $resp['dequeueCount'];
        $msg->firstDequeueTime = $resp['firstDequeueTime'];
        return $msg;
    }

    /* 删除消息

        @type receipt_handle: string
        @param receipt_handle: 最近一次操作该消息返回的临时句柄
    */

    public function batch_receive_message($num_of_msg, $polling_wait_seconds = NULL)
    {
        $params = array('queueName' => $this->queue_name, 'numOfMsg' => $num_of_msg);
        if ($polling_wait_seconds != NULL) {
            $params['UserpollingWaitSeconds'] = $polling_wait_seconds;
            $params['pollingWaitSeconds']     = $polling_wait_seconds;
        } else {
            $params['UserpollingWaitSeconds'] = 30;
        }
        $msgInfoList    = $this->cmq_client->batch_receive_message($params);
        $retMessageList = array();
        foreach ($msgInfoList as $msg) {
            $retmsg = new Message();
            if ($this->encoding) {
                $retmsg->msgBody = base64_decode($msg['msgBody']);
            } else {
                $retmsg->msgBody = $msg['msgBody'];
            }
            $retmsg->msgId            = $msg['msgId'];
            $retmsg->receiptHandle    = $msg['receiptHandle'];
            $retmsg->enqueueTime      = $msg['enqueueTime'];
            $retmsg->nextVisibleTime  = $msg['nextVisibleTime'];
            $retmsg->dequeueCount     = $msg['dequeueCount'];
            $retmsg->firstDequeueTime = $msg['firstDequeueTime'];
            $retMessageList []        = $retmsg;
        }
        return $retMessageList;
    }

    /* 批量删除消息

        @type receipt_handle_list: list
        @param receipt_handle_list: batch_receive_message返回的多条消息的临时句柄
    */

    public function delete_message($receipt_handle)
    {
        $params = array('queueName' => $this->queue_name, 'receiptHandle' => $receipt_handle);
        $this->cmq_client->delete_message($params);
    }

    public function batch_delete_message($receipt_handle_list)
    {
        $params = array('queueName' => $this->queue_name);
        $n      = 1;
        foreach ($receipt_handle_list as $receipt_handle) {
            $key          = 'receiptHandle.' . $n;
            $params[$key] = $receipt_handle;
            $n            += 1;
        }
        $this->cmq_client->batch_delete_message($params);
    }
}


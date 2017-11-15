<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class Topic
{
    private $topic_name;
    private $cmq_client;
    private $encoding;

    public function __construct($topic_name, CMQClient $cmq_client, $encoding = false)
    {
        $this->topic_name = $topic_name;
        $this->cmq_client = $cmq_client;
        $this->encoding   = $encoding;
    }

    public function set_encoding($encoding)
    {
        $this->encoding = $encoding;
    }


    /*
     * create topic 
     * @type topic_meta : TopicMeta
     * @param topic_meta :
     */
    public function create($topic_meta)
    {
        $params = array(
            'topicName'  => $this->topic_name,
            'filterType' => $topic_meta->filterType
        );

        if ($topic_meta->maxMsgSize > 0) {
            $params['maxMsgSize'] = $topic_meta->maxMsgSize;
        }
        $this->cmq_client->create_topic($params);
    }

    /*
     * get attributes
     * 
     * @return topic_meta :TopicMeta
     * 
     */
    public function get_attributes()
    {
        $params = array(
            'topicName' => $this->topic_name,
        );
        $resp   = $this->cmq_client->get_topic_attributes($params);

        $topic_meta = new TopicMeta();
        $this->__resp2meta($topic_meta, $resp);

        return $topic_meta;
    }


    /*
     * set attributes 
     * 
     * @type topic_meta :TopicMeta
     * @param topic_meta :
     */

    protected function __resp2meta($topic_meta, $resp)
    {
        if (isset($resp['maxMsgSize'])) {
            $topic_meta->maxMsgSize = $resp['maxMsgSize'];
        }
        if (isset($resp['msgRetentionSeconds'])) {
            $topic_meta->msgRetentionSeconds = $resp['msgRetentionSeconds'];
        }
        if (isset($resp['createTime'])) {
            $topic_meta->createTime = $resp['createTime'];
        }
        if (isset($resp['lastModifyTime'])) {
            $topic_meta->lastModifyTime = $resp['lastModifyTime'];
        }
        if (isset($resp['filterType'])) {
            $topic_meta->filterType = $resp['filterType'];
        }


    }


    /*
     * delete topic 
     */

    public function set_attributes($topic_meta)
    {

        $params = array(
            'topicName'  => $this->topic_name,
            'maxMsgSize' => strval($topic_meta->maxMsgSize)
        );
        $this->cmq_client->set_topic_attributes($params);
    }

    /*
     * 推送消息 非批量
     * @type message :string
     * @param message
     * 
     * @type vTagList :list
     * @param vTagList 标签
     * 
     * @return   message handle 
     */

    public function delete()
    {
        $params = array(
            'topicName' => $this->topic_name
        );
        $this->cmq_client->delete_topic($params);
    }


    /*
     * 批量推送消息
     * @type vmessageList :list
     * @param vmessageList:
     *
     * @type vtagList :list
     * @param vtagList
     *
     * @return : return message handle list
     */

    public function publish_message($message, $vTagList = null, $routingKey = null)
    {
        $params = array(
            'topicName' => $this->topic_name,
            'msgBody'   => $message,
        );
        if ($routingKey != null) {
            $params['routingKey'] = $routingKey;
        }
        if ($vTagList != null && is_array($vTagList) && !empty($vTagList)) {
            $n = 1;
            foreach ($vTagList as $tag) {
                $key          = 'msgTag.' . $n;
                $params[$key] = $tag;
                $n            += 1;
            }
        }
        $msgId = $this->cmq_client->publish_message($params);

        return $msgId;
    }

    /* 列出Topic的Subscriptoin
    
    @type topic_name :string
    @param topic_name:
    
    @type searchWord: string
    @param searchWord:  订阅关键字
    
    @type limit: int
    @param limit: 最多返回的订阅数目
    
    @type offset: string
    @param offset: list_subscription的起始位置，上次list_subscription返回的next_offset
    
    @rtype: tuple
    @return: subscriptionURL的列表和下次list subscription的起始位置; 如果所有subscription都list出来，next_offset为"".
    */

    public function batch_publish_message($vmessageList, $vtagList = null, $routingKey = null)
    {
        $params = array(
            'topicName' => $this->topic_name,
        );

        if ($routingKey != null) {
            $params['routingKey'] = $routingKey;
        }
        $n = 1;
        if (is_array($vmessageList) && !empty($vmessageList)) {
            foreach ($vmessageList as $msg) {
                $key = 'msgBody.' . $n;
                if ($this->encoding) {
                    $params[$key] = base64_encode($msg);
                } else {
                    $params[$key] = $msg;
                }
                $n += 1;
            }
        }
        if ($vtagList != null && is_array($vtagList) && !empty($vtagList)) {
            $n = 1;
            foreach ($vtagList as $tag) {
                $key          = 'msgTag.' . $n;
                $params[$key] = $tag;
                $n            += 1;
            }
        }

        $msgList = $this->cmq_client->batch_publish_message($params);

        $retMessageList = array();
        foreach ($msgList as $msg) {
            if (isset($msg['msgId'])) {
                $retmsgId          = $msg['msgId'];
                $retMessageList [] = $retmsgId;
            }
        }
        return $retMessageList;

    }

    public function list_subscription($searchWord = "", $limit = -1, $offset = "")
    {
        $params = array('topicName' => $this->topic_name);

        if ($searchWord != "") {
            $params['searchWord'] = $searchWord;
        }

        if ($limit != -1) {
            $params['limit'] = $limit;
        }

        if ($offset != "") {
            $params['offset'] = $offset;
        }

        $resp = $this->cmq_client->list_subscription($params);

        if ($offset == "") {
            $next_offset = count($resp['subscriptionList']);
        } else {
            $next_offset = (int)$offset + count($resp['subscriptionList']);
        }

        if ($next_offset >= $resp['totalCount']) {
            $next_offset = "";
        }

        return array("totalCoult"       => $resp['totalCount'],
                     "subscriptionList" => $resp['subscriptionList'],
                     "next_offset"      => $next_offset);
    }
}


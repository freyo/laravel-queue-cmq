<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class CMQClient
{
    private $host;
    private $secretId;
    private $secretKey;
    private $version;
    private $http;
    private $method;
    private $URISEC = '/v2/index.php';

    public function __construct($host, $secretId, $secretKey, $version = "SDK_PHP_1.3", $method = "POST")
    {
        $this->process_host($host);
        $this->secretId    = $secretId;
        $this->secretKey   = $secretKey;
        $this->version     = $version;
        $this->method      = $method;
        $this->sign_method = 'HmacSHA1';
        $this->http        = new CMQHttp($this->host);
    }

    protected function process_host($host)
    {
        if (strpos($host, "http://") === 0) {
            $_host = substr($host, 7, strlen($host) - 7);
        } elseif (strpos($host, "https://") === 0) {
            $_host = substr($host, 8, strlen($host) - 8);
        } else {
            throw new CMQClientParameterException("Only support http(s) prototol. Invalid endpoint:" . $host);
        }
        if ($_host[strlen($_host) - 1] == "/") {
            $this->host = substr($_host, 0, strlen($_host) - 1);
        } else {
            $this->host = $_host;
        }
    }

    public function set_sign_method($sign_method = 'sha1')
    {
        if ($sign_method == 'sha1' || $sign_method == 'HmacSHA256')
            $this->sign_method = 'HmacSHA1';
        elseif ($sign_method == 'sha256')
            $this->sign_method = 'HmacSHA256';
        else
            throw new CMQClientParameterException('Only support sign method HmasSHA256 or HmacSHA1 . Invalid sign method:' . $sign_method);

    }

    public function set_method($method = 'POST')
    {
        $this->method = $method;
    }

    public function set_connection_timeout($connection_timeout)
    {
        $this->http->set_connection_timeout($connection_timeout);
    }

    public function set_keep_alive($keep_alive)
    {
        $this->http->set_keep_alive($keep_alive);
    }

    public function create_queue($params)
    {
        $resp_inter = $this->request('CreateQueue', $params);
        $this->check_status($resp_inter);
    }

    protected function request($action, $params)
    {
        // make request internal
        $req_inter = new RequestInternal($this->method, $this->URISEC);
        $this->build_req_inter($action, $params, $req_inter);

        $iTimeout = 0;

        if (array_key_exists("UserpollingWaitSeconds", $params)) {
            $iTimeout = (int)$params['UserpollingWaitSeconds'];
        }
        // send request
        $resp_inter = $this->http->send_request($req_inter, $iTimeout);

        return $resp_inter;
    }

    protected function build_req_inter($action, $params, &$req_inter)
    {
        $_params                  = $params;
        $_params['Action']        = ucfirst($action);
        $_params['RequestClient'] = $this->version;

        if (!isset($_params['SecretId']))
            $_params['SecretId'] = $this->secretId;

        if (!isset($_params['Nonce']))
            $_params['Nonce'] = rand(1, 65535);

        if (!isset($_params['Timestamp']))
            $_params['Timestamp'] = time();

        if (!isset($_params['SignatureMethod']))
            $_params['SignatureMethod'] = $this->sign_method;

        $plainText            = Signature::makeSignPlainText($_params,
            $this->method, $this->host, $req_inter->uri);
        $_params['Signature'] = Signature::sign($plainText, $this->secretKey, $this->sign_method);

        $req_inter->data = http_build_query($_params);
        $this->build_header($req_inter);
    }

    protected function build_header(&$req_inter)
    {
        if ($this->http->is_keep_alive()) {
            $req_inter->header[] = 'Connection: Keep-Alive';
        }

        $req_inter->header[] = 'Expect:';
    }

//===============================================queue operation===============================================

    protected function check_status($resp_inter)
    {
        if ($resp_inter->status != 200) {
            throw new CMQServerNetworkException($resp_inter->status, $resp_inter->header, $resp_inter->data);
        }

        $resp      = json_decode($resp_inter->data, TRUE);

        $code      = $resp['code'];
        $message   = $resp['message'];
        $requestId = isset($resp['requestId']) ? $resp['requestId'] : null;

        if ($code != 0) {
            throw new CMQServerException($message, $requestId, $code, $resp);
        }
    }

    public function delete_queue($params)
    {
        $resp_inter = $this->request('DeleteQueue', $params);
        $this->check_status($resp_inter);
    }

    public function rewindQueue($params)
    {
        $resp_inter = $this->request('RewindQueue', $params);
        $this->check_status($resp_inter);
    }

    public function list_queue($params)
    {
        $resp_inter = $this->request('ListQueue', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function set_queue_attributes($params)
    {
        $resp_inter = $this->request('SetQueueAttributes', $params);
        $this->check_status($resp_inter);
    }

    public function get_queue_attributes($params)
    {
        $resp_inter = $this->request('GetQueueAttributes', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function send_message($params)
    {
        $resp_inter = $this->request('SendMessage', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret['msgId'];
    }

    public function batch_send_message($params)
    {
        $resp_inter = $this->request('BatchSendMessage', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret['msgList'];
    }

    public function receive_message($params)
    {
        $resp_inter = $this->request('ReceiveMessage', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function batch_receive_message($params)
    {
        $resp_inter = $this->request('BatchReceiveMessage', $params);
        $this->check_status($resp_inter);

        $ret = json_decode($resp_inter->data, TRUE);
        return $ret['msgInfoList'];
    }

    public function delete_message($params)
    {
        $resp_inter = $this->request('DeleteMessage', $params);
        $this->check_status($resp_inter);
    }

    public function batch_delete_message($params)
    {
        $resp_inter = $this->request('BatchDeleteMessage', $params);
        $this->check_status($resp_inter);
    }

    //=============================================topic operation================================================

    public function create_topic($params)
    {
        $resp_inter = $this->request("CreateTopic", $params);
        $this->check_status($resp_inter);
    }

    public function delete_topic($params)
    {
        $resp_inter = $this->request("DeleteTopic", $params);
        $this->check_status($resp_inter);
    }

    public function list_topic($params)
    {
        $resp_inter = $this->request("ListTopic", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function set_topic_attributes($params)
    {
        $resp_inter = $this->request("SetTopicAttributes", $params);
        $this->check_status($resp_inter);
    }

    public function get_topic_attributes($params)
    {
        $resp_inter = $this->request("GetTopicAttributes", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function publish_message($params)
    {
        $resp_inter = $this->request("PublishMessage", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function batch_publish_message($params)
    {
        $resp_inter = $this->request("BatchPublishMessage", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    //============================================subscription operation=============================================
    public function create_subscription($params)
    {
        $resp_inter = $this->request("Subscribe", $params);
        $this->check_status($resp_inter);
    }

    public function clear_filterTags($params)
    {
        $resp_inter = $this->request("ClearSubscriptionFilterTags", $params);
        $this->check_status($resp_inter);
    }

    public function delete_subscription($params)
    {
        $resp_inter = $this->request("Unsubscribe", $params);
        $this->check_status($resp_inter);
    }

    public function get_subscription_attributes($params)
    {
        $resp_inter = $this->request("GetSubscriptionAttributes", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }

    public function set_subscription_attributes($params)
    {
        $resp_inter = $this->request("SetSubscriptionAttributes", $params);
        $this->check_status($resp_inter);
    }

    public function list_subscription($params)
    {
        $resp_inter = $this->request("ListSubscriptionByTopic", $params);
        $this->check_status($resp_inter);
        $ret = json_decode($resp_inter->data, TRUE);
        return $ret;
    }
}

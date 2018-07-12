<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class CMQServerNetworkException extends CMQExceptionBase
{
    //服务器网络异常

    public $status;
    public $header;
    public $data;

    public function __construct($status = 200, $header = null, $data = '')
    {
        if ($header == null) {
            $header = [];
        }
        $this->status = $status;
        $this->header = $header;
        $this->data = $data;
    }

    public function __toString()
    {
        $info = ['status'      => $this->status,
                      'header' => json_encode($this->header),
                      'data'   => $this->data, ];

        return 'CMQServerNetworkException  '.json_encode($info);
    }
}

<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class ResponseInternal
{
    public $header;
    public $status;
    public $data;

    public function __construct($status = 0, $header = null, $data = '')
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

        return json_encode($info);
    }
}

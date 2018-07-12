<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class RequestInternal
{
    public $header;
    public $method;
    public $uri;
    public $data;

    public function __construct($method = '', $uri = '', $header = null, $data = '')
    {
        if ($header == null) {
            $header = [];
        }
        $this->method = $method;
        $this->uri = $uri;
        $this->header = $header;
        $this->data = $data;
    }

    public function __toString()
    {
        $info = ['method'      => $this->method,
                      'uri'    => $this->uri,
                      'header' => json_encode($this->header),
                      'data'   => $this->data, ];

        return json_encode($info);
    }
}

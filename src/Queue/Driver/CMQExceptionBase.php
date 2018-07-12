<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

use RuntimeException;

class CMQExceptionBase extends RuntimeException
{
    /*
    @type code: int
    @param code: 错误类型

    @type message: string
    @param message: 错误描述

    @type data: array
    @param data: 错误数据
    */

    public $code;
    public $message;
    public $data;

    public function __construct($message, $code = -1, $data = [])
    {
        parent::__construct($message, $code);
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function __toString()
    {
        return 'CMQExceptionBase  '.$this->get_info();
    }

    public function get_info()
    {
        $info = ['code'         => $this->code,
                      'data'    => json_encode($this->data),
                      'message' => $this->message, ];

        return json_encode($info);
    }
}

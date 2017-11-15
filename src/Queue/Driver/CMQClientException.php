<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;

class CMQClientException extends CMQExceptionBase
{
    public function __construct($message, $code = -1, $data = array())
    {
        parent::__construct($message, $code, $data);
    }

    public function __toString()
    {
        return "CMQClientException  " . $this->get_info();
    }
}

<?php

namespace Freyo\LaravelQueueCMQ\Queue\Driver;


class CMQServerException extends CMQExceptionBase
{
    /* cmq处理异常

        @note: 根据code进行分类处理，常见错误类型：
             : 4000       参数不合法
             : 4100       鉴权失败:密钥不存在/失效
             : 4300       账户欠费了
             : 4400       消息大小超过队列属性设置的最大值
             : 4410       已达到队列最大的消息堆积数
             : 4420       qps限流
             : 4430       删除消息的句柄不合法或者过期了
             : 4440       队列不存在
             : 4450       队列个数超过限制
             : 4460       队列已经存在
             : 6000       服务器内部错误
             : 6010       批量删除消息失败（具体原因还要看每个消息删除失败的错误码）
             : 7000       空消息，即队列当前没有可用消息
             : 更多错误类型请登录腾讯云消息服务官网进行了解；
    */

    public $request_id;

    public function __construct($message, $request_id, $code = -1, $data = array())
    {
        parent::__construct($message, $code, $data);
        $this->request_id = $request_id;
    }

    public function __toString()
    {
        return "CMQServerException  " . $this->get_info() . ", RequestID:" . $this->request_id;
    }
}

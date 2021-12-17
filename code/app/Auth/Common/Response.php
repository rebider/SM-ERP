<?php

namespace App\Auth\Common;
class Response
{
    /**
     * 消息
     * @var string
     */
    public $message;

    /**
     * 状态（true成功，false失败）
     * @var bool
     */
    public $status;

    /**
     * 数据
     * @var object
     */
    public $data;

    /**
     * 返回成功
     * @param string $message    消息
     * @param string|array $data 数据
     * @return Response
     */
    public static function isSuccess($message = null, $data = null)
    {
        $response = new Response();
        $response->status = true;
        $response->message = $message;
        $response->data = $data;
        return $response;
    }

    /**
     * 返回失败
     * @param string $message    消息
     * @param string|array $data 数据
     * @return Response
     */
    public static function isFailure($message = null, $data = null)
    {
        $response = new Response();
        $response->status = false;
        $response->message = $message;
        $response->data = $data;
        return $response;
    }
}
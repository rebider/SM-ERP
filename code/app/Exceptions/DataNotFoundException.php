<?php

namespace App\Exceptions;

use Exception;

/**
 * 访问无效数据类
 */
class DataNotFoundException extends Exception
{
    protected $message = 'DataNotFound';       //异常信息
    protected $code = 888;                     //用户自定义异常代码
    protected $file;                           //发生异常的文件名
    protected $line;                           //发生异常的代码行号

    public function __construct($message = "Data not found", $code = 888)
    {
        $this->message = $message;
        $this->code = $code;
        parent::__construct($message, $this->code);
    }

    public function __toString()
    {
        return __CLASS__ . ':[' . $this->code . ']:' . $this->message . '\n';
    }
}
<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Logs
 * 记录日志帮助类
 *
 */
class LogHelper extends Model
{
//    protected $connection = 'mongodb';

    //指定id
    protected $primaryKey = 'RequestId';

    const ERRORTABLE = 'ERPErrorLogs' ;
    //回调日志
    const NOTIFY = 'ERPNotifyLogs';

    const LOGTYPE = [
        'task' =>'ERPTaskLogs',
        'api' =>'ERPAPILogs',
        'notify' =>'ERPNotifyLogs',
    ];

    private static $systemCode = 'sumao_erp';

    /**
     * @param $exceptionInfo
     * @param $exceptionType
     * Note: 文件日志
     * Data: 2019/5/21 10:05
     * Author: zt7785
     */
    public static function setExceptionLog($exceptionInfo,$exceptionType)
    {
        $currentData = date('Y-m-d');
        $dir = 'Exception'.DIRECTORY_SEPARATOR.$currentData;
        $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_Exception';
        if (strtolower($exceptionType) == 'orders') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersTask';
        } elseif (strtolower($exceptionType) == 'orders_api') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersAPI';
        } elseif (strtolower($exceptionType) == 'orders_item') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersItemAPI';
        } elseif (strtolower($exceptionType) == 'updateshippinglogics') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_UpdateShippingLogics';
        }
        $filePath .='.log';
        Storages::except_log(json_encode($exceptionInfo,JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES),$dir,$filePath);
    }


    /**
     * @param $exceptionInfo
     * @param $exceptionType
     * Note: 正常日志
     * Data: 2019/6/29 11:10
     * Author: zt7785
     */
    public static function setSuccessLog($exceptionInfo,$exceptionType)
    {
        $currentData = date('Y-m-d');
        $dir = 'Success'.DIRECTORY_SEPARATOR.$currentData;
        $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_Success';
        if (strtolower($exceptionType) == 'orders') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersTask';
        } elseif (strtolower($exceptionType) == 'orders_api') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersAPI';
        } elseif (strtolower($exceptionType) == 'orders_item') {
            $filePath = $dir.DIRECTORY_SEPARATOR.$currentData.'_OrdersItemAPI';
        }
        $filePath .='.log';
        Storages::except_log(json_encode($exceptionInfo,JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES),$dir,$filePath);
    }



    /**
     * author: ZT3361
     * 获取请求ip
     * @return array|false|string
     */
    private static function clientIp()
    {
        $ip = '';
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }

        return $ip;
    }

    /**
     * author: ZT3361
     * 记录info日志
     * @param null $responseData
     * @param null $requestData
     */
    public static function info($responseData = null, $requestData = null ,$logtype = 'task' )
    {
        return false;
        $mongoTab = self::LOGTYPE [$logtype];
        if (is_array($requestData) || is_object($requestData)) {
            $requestData = json_encode($requestData,JSON_UNESCAPED_UNICODE);
        }

        if (is_array($responseData) || is_object($responseData)) {
            $responseData = json_encode($responseData,JSON_UNESCAPED_UNICODE);
        }

        $values =[
            "SystemCode" => self::$systemCode,
            "RequestId" => uniqid(),
            "Level" => "INFO",
            "RequestUrl" => ($_SERVER['HTTP_HOST']??"") . ($_SERVER['REQUEST_URI']??""),
            "IP" => self::clientIp(),
            "RequestData" => $requestData,
            "ResponseData" => $responseData,
            "RequestTime" => date('Y-m-d h:i:s',time()),
            "ResponseTime" => date('Y-m-d h:i:s',time()),
            "Exception" => null
        ];
//        $mongodb = DB::connection('mongodb');
//        $db = $mongodb->collection($mongoTab);
//        $db->insert($values) ;
//        unset($mongodb);
    }

    /**
     * author: ZT3361
     * 记录error日志
     * @param null $responseData
     * @param null $requestData
     */
    public static function error($requestData = null, $exception = null)
    {
        return false;
        if (is_array($requestData)) {
            $requestData = json_encode($requestData,JSON_UNESCAPED_UNICODE);
        }

        if (is_array($exception)) {
            $exception = json_encode($exception,JSON_UNESCAPED_UNICODE);
        }

        $values =[
            "SystemCode" => self::$systemCode,
            "RequestId" => uniqid(),
            "Level" => "ERROR",
            "RequestUrl" => ($_SERVER['HTTP_HOST']??"") . ($_SERVER['REQUEST_URI']??""),
            "IP" => self::clientIp(),
            "RequestData" => $requestData,
            "ResponseData" => null,
            "RequestTime" => date('Y-m-d h:i:s',time()),
            "ResponseTime" => date('Y-m-d h:i:s',time()),
            "Exception" => $exception
        ];
//        $mongodb = DB::connection('mongodb');
//        $db = $mongodb->collection(self::ERRORTABLE);
//        $db->insert($values);
//        unset($mongodb);
    }
}
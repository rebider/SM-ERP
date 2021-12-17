<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;

/**
 * Class Storages
 * Notes: 文件存储
 * @package App
 * Data: 2018/8/4 17:26
 * Author: zt7785
 */
class Storages
{
    /**
     * 文件日志
     * @param $content 要写入的内容
     * @param string $file 日志文件
     */
    public static function except_log($content,$dir='Exception',$file = "log.txt" )
    {
            $storage = Storage::disk('local');
            if (!$storage->exists($dir)) {
                $storage->makeDirectory($dir);
            }
        file_put_contents(storage_path('app'.DIRECTORY_SEPARATOR.$file), $content.PHP_EOL, FILE_APPEND);
    }

    /**
     * Note: 保留七天以内的文件
     * Data: 2018/8/22 17:50
     * Author: zt7785
     */
    public static function unlinkFile () {
        //2018-07-19_collection //采集队列 及异常
        //2018-07-23_exceptGoods //异常商品
        //2018-08-11_exception 价格 队列 节点
        //2018-08-22_priceFormula
        //TODO
        //删除文件
        $date = date("Y-m-d",strtotime("-7 day"));
        $fileArr = ['_collection','_exceptGoods','_exception','_priceFormula'];
        foreach ($fileArr as $v) {
            $dir = storage_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$date.$v;
            if (!$handle = @opendir($dir)) {
                continue;
            }
            while (false !== ($file = readdir($handle))) {
                if ($file !== "." && $file !== "..") {       //排除当前目录与父级目录
                    $file = $dir . '/' . $file;
                    if (is_dir($file)) {
                        self::unlinkFile($file);
                    } else {
                        @unlink($file);
                    }
                }
            }
            @rmdir($dir);
        }
//        $storage = Storage::disk('local');
//        $date = date("Y-m-d",strtotime("-7 day"));
//        $fileArr = ['_collection','_exceptGoods','_exception','_priceFormula'];
//        foreach ($fileArr as $value) {
//            if ($storage->exists($date.$value)) {
//                 $storage->deleteDirectory($date.$value);
//            }
//        }
    }

    /**
     * Note: 每月偶数天删除mongo日志记录
     * Data: 2019/2/27 9:36
     * Author: zt7785
     */
    public static function unlinkMongoFile () {
        //TODO
        //删除文件
        $date = date("j");
        if ($date % 2 == 0) {
            $fileArr = ['MongoDb_LogInfo.log','MongoDb_Exception.log'];
            foreach ($fileArr as $v) {
                $filePath = storage_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'MongoDb'.DIRECTORY_SEPARATOR.$v;
                @unlink($filePath);
            }
        }
    }
    /**
     * @param $path
     * @param $content
     * Note: GZ压缩存储
     * Data: 2018/11/8 16:26
     * Author: zt7785
     */
    public static function setGzFile($path,$content)
    {
            $storage = Storage::disk('local');
            if (!$storage->exists('Cus_Gz')) {
                $storage->makeDirectory('Cus_Gz');
            }
        file_put_contents(storage_path('app'.DIRECTORY_SEPARATOR.$path), $content.PHP_EOL, FILE_APPEND);
    }

    /**
     * @param $path
     * @param $content
     * Note: GZ解压
     * Data: 2018/11/8 16:26
     * Author: zt7785
     */
    public static function getGzFile($path)
    {
        $storage = Storage::disk('local');
        if (!$storage->exists('Cus_Gz')) {
            $storage->makeDirectory('Cus_Gz');
        }
        $data = [];
        if ($storage->exists($path)) {
            $data = $storage->get($path);
        }
        return $data;
    }
}

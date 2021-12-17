<?php

namespace App\Http\Controllers\UnitTesting;

use App\Common\Common;
use App\Models\DingRobotWarn;
use App\Models\LogHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UnitTestingController extends Controller
{
    public $responseData = [
        'code'  => 400,
        'msg'   => '',
        'data'  => '',
    ];

    /**
     * @return string
     * Note: mongo测试
     * Data: 2019/5/22 16:31
     * Author: zt7785
     */
    public function mongoTest () {
        if (!extension_loaded('mongodb')) {
            $this->responseData ['msg'] = 'mongodb拓展异常';
            return json_encode($this->responseData,JSON_UNESCAPED_UNICODE);
        }
        $int = 1;
        try {
            $int / 0;
            $this->responseData ['code'] = '200';
            $this->responseData ['msg'] = 'mongodb单元测试成功';
        } catch (\Exception $exception ) {
            $exception_data = [
                'msg'                       => '失败信息：' . $exception->getMessage(),
                'line'                      => '失败行数：' . $exception->getLine(),
                'file'                      => '失败文件：' . $exception->getFile(),
            ];

            $exceptionDing ['type'] = 'task';
            $dingPushData ['task'] = 'mongodb单元测试';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            $exceptionDing ['path'] = __FUNCTION__;
            DingRobotWarn::robot($exceptionDing,$dingPushData);
            LogHelper::info($exception_data,null,$exceptionDing ['type']);
            $this->responseData ['code'] = '400';
            $this->responseData ['msg'] = 'mongodb单元测试失败';
        }
        return json_encode($this->responseData,JSON_UNESCAPED_UNICODE);
    }

    /**
     * Note: PHP 拓展
     * Data: 2019/7/8 11:41
     * Author: zt7785
     */
    public function deployCheck () {
        $version = DB::select('select VERSION() version');
        echo "MYSQL版本:".$version [0] ->version;
        phpinfo();
    }

    /**
     * @param Request $request
     * @return string
     * Note: 图片资源
     * Data: 2019/7/8 15:13
     * Author: zt7785
     */
    public function linkTest (Request $request) {
        $realpath = $request->get('path');
        if (empty($realpath)) {
            $this->responseData ['code'] = '400';
            $this->responseData ['msg'] = '缺少必要参数:path';
            return json_encode($this->responseData,JSON_UNESCAPED_UNICODE);
        }
        if(!Storage::disk('upload_imgs')->exists($realpath)){
            //报404错误
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found"); exit;
        }
        //输出图片
        header('Content-type: image/jpg');
        echo Storage::disk('upload_imgs')->get ($realpath);// 获取文件内容;
    }

    /**
     * Note: 丁丁推送测试
     * Data: 2019/7/8 15:14
     * Author: zt7785
     */
    public function dingPush () {
        $exception_data = [
            'start_time'                => date('Y-m-d H:i:s'),
            'msg'                       => '丁丁推送测试',
        ];
        $exception ['type'] = 'task';
        $exception ['path'] = __FUNCTION__;
        $dingPushData ['task'] = '丁丁推送测试';
        $dingPushData ['message'] = $exception_data ['start_time']."\n\n".$exception_data ['msg'];
        DingRobotWarn::robot($exception,$dingPushData);
    }

}

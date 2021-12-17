<?php

namespace App\Models;


/**
 * Class DingRobotWarn
 * Notes: 丁丁机器人
 * Data: 2018/10/30 18:13
 * Author: zt7785
 */
class DingRobotWarn
{
    public $CurlHandle = '';

    public $robotType = [
        'task'=>'定时任务',
        'api'=>'接口服务',
    ];

    public $exceptionPath = '';

    public function __construct()
    {
        $this->CurlHandle = new HttpCurl();
    }

    /**
     * @param $exception array type['task','api'] path['定时任务signature','api接口地址']
     * @param $content array ['task 推送名','message 推送信息']
     * @param string $trace_id 异常表id
     * Note: 丁丁常规推送
     * Data: 2019/5/21 9:36
     * Author: zt7785
     */
    public static function robot($exception,$content,$trace_id = '')
    {
        $dingRobot = new self();
        $title = "速贸天下云仓平台".$dingRobot->robotType[$exception['type']]."数据异常提醒";
        $all_message        = "## ".$title."\n\n"
                            ."> task: {$content['task']} \n\n"
                            ."> exceptionPath: {$exception['path']} \n\n"
                            ."> message: {$content['message']} \n\n";
            if ($trace_id) {
                //异常数据记录id
                $all_message .="> trace: 异常信息记录 ID :$trace_id";
            }

            $data = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => $title,
                    'text' => $all_message
                ]
            ];
            $dingRobotUrl = config('services.ding_robot.url');
            $dingRobot->CurlHandle->setParams(json_encode($data));
            $dingRobot->CurlHandle->post($dingRobotUrl,'json',true);
    }

}

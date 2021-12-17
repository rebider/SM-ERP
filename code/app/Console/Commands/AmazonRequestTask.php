<?php

namespace App\Console\Commands;

use App\Models\AmazonApiServiceRequest;
use App\Models\AmazonApiServiceTask as Task;
use App\Models\DingRobotWarn;
use App\Models\LogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AmazonRequestTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AmazonTask:AmazonRequest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '亚马逊请求任务';


    protected $task_name = 'AmazonRequestTask';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ignore_user_abort(true);
        set_time_limit (0);
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
        $current_times = strtotime(date('Y-m-d'));
        $taskInfo = Task::getTaskInfo($this->task_name,$current_times);
        if (!empty($taskInfo) && empty($taskInfo['end_time'])) {
            echo $this->signature.' 上次任务未结束!' . "\r\n";
            return ;
        }
        $current_time = date('Y-m-d H:i:s');
        $search_time = date('Y-m-d H:i:s',strtotime('-2 day'));
        //两天之内 未处理的上传数据
        $AmazonApiServiceRequest = new AmazonApiServiceRequest();
        $feedInfos = $AmazonApiServiceRequest->with('SettingShops')->whereDate('created_at','>',$search_time)->where('is_finished','!=',AmazonApiServiceRequest::IS_FINISHED)->get();
//        $feedInfos = $AmazonApiServiceRequest->with('SettingShops')->whereDate('created_at','>',$search_time)->where('is_finished','!=',AmazonApiServiceRequest::IS_FINISHED)->where('id',14)->get();
        if ($feedInfos->isEmpty()) {
            echo ' 无亚马逊请求信息!' . "\r\n";
            return false;
        }
        $feedInfos = $feedInfos->toArray();
        $task_name = $this->task_name;
        $task_data ['start_time'] = time();
        $taskInfo = Task::setTask($task_name,$task_data,$current_times);
        DB::connection()->disableQueryLog();
        try {
            $AmazonApiServiceRequest->feedSubmitRequestLogic($feedInfos,$current_time);
            $task_name = $this->task_name;
            $task_data ['end_time'] = time();
            Task::setTask($task_name,$task_data,$current_times,$taskInfo);
        } catch (\Exception $e) {
                $task_name = $this->task_name;
                $task_data ['end_time'] = time();
                Task::setTask($task_name,$task_data,$current_times,$taskInfo);

                echo '发生错误!失败信息: '.$e->getMessage() . "\r\n";
                $exception_data = [
                'start_time'                => $current_time,
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
                ];
                $exception ['path'] = __FUNCTION__;
                LogHelper::setExceptionLog($exception_data,$exception ['path']);
                $exception ['type'] = 'task';
                $dingPushData ['task'] = 'AmazonFeed接口请求任务';
                $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data,null,$exception ['type']);
            }
    }
}

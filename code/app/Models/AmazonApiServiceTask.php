<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmazonApiServiceTask extends Model
{
    protected $table = 'amazon_api_service_task';

    public $timestamps = true;

    public $fillable = ['id','task_name','task_date','task_nums','start_time','end_time','created_at','updated_at'];

    /**
     * @param $task_name
     * @param $data
     * @return Model
     * Note:处理定时任务
     * Data: 2018/11/13 13:45
     * Author: zt7785
     */
    public static function setTask ($task_name,$data,$current_times,$taskInfo = [] ) {
        if (empty($taskInfo)) {
            $taskInfo = self::where([['task_date',$current_times],['task_name',$task_name]])->first();
            $taskId = $taskInfo ? $taskInfo ['id'] : 0 ;
        } else {
            $taskId = $taskInfo ['id'];
        }

        if (isset($data['start_time']) && !isset($data['end_time'])) {
            //任务开始
            $data ['end_time'] = 0 ;
            $data['task_nums'] = $taskInfo ? $taskInfo['task_nums'] + 1 : 1;
        }
        if (isset($data['start_time']) && isset($data['end_time']) ) {
            $data['task_nums'] = $taskInfo ? $taskInfo['task_nums'] + 1 : 1;
        }
        $data['task_date'] = $current_times;
        $data['task_name'] = $task_name;
        return self::postGoods($data,$taskId);
    }

    public static function postGoods ($data,$id = 0) {
        return self::updateOrCreate(['id'=>$id],$data);
    }

    /**
     * @param $task_name
     * @return Model|null|static
     * Note: 获取定时任务信息
     * Data: 2018/11/13 13:45
     * Author: zt7785
     */
    public static function getTaskInfo ($task_name,$current_times) {
        return self::where([['task_date',$current_times],['task_name',$task_name]])->first(['id','end_time']);
    }
}

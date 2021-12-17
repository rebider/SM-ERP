<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

/**
 * Class RulesOrderTrouble
 * Notes: 订单问题规则
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class RulesOrderTrouble extends Model
{
    protected $table = 'rules_order_trouble';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','user_id','trouble_type_id','opening_status','trouble_rules_name','trouble_desc','created_at','updated_at','is_deleted'];

    /**
     * @var 搞基设置id
     */
    const RULES_ORDER_MENUS_ID = 5;

    /**
     * @var 开启
     */
    const STATUS_OPENING = 1;
    /**
     * @var 关闭
     */
    const STATUS_CLOSEDING = 2;

    /**
     * @var 已删除
     */
    const IS_DELETED = 1;
    /**
     * @var 未删除
     */
    const UN_DELETED = 0;

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    /**
     * @return $this
     * Note: 问题类型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function RulesOrderTroubleType()
    {
        return $this->belongsTo(RulesOrderTroubleType::class, 'trouble_type_id', 'id');
    }


    /**
     * @return $this
     * Note: 问题条件设置
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function RulesTroubleCondition()
    {
        return $this->hasMany(RulesTroubleCondition::class,'trouble_rule_id','id');

    }

    /**
     * @param $param
     * @param int $offset
     * @param int $limit
     * @return mixed
     * Note: 订单规则列表数据搜索
     * Data: 2019/3/13 18:28
     * Author: zt7785
     */
    public static function getTroublesDatas($param, $offset = 1, $limit = 0)
    {
        $collection = self::with('RulesOrderTroubleType');

        $collection->where('is_deleted',self::UN_DELETED);
        //关联状态下的条件
//        $collection->whereHas('RulesTroubleCondition',function ($query) {
//            $query->where('is_used',RulesTroubleCondition::STATUS_OPENING);
//        });

        $collection->with(['RulesTroubleCondition'=>function ($query) {
            $query->where('is_used',RulesTroubleCondition::STATUS_OPENING);
        }]);

        //用户id
        if (isset($param['user_id'])) {
            $param['user_id'] && $collection->where('user_id',$param['user_id']);
        }
        //开启状态
        $param['opening_status'] && $collection->where('opening_status',$param['opening_status']);
        //规则名称 暂未模糊 0402 V2.13 F20.0 模糊查询
        $param['trouble_rules_name'] && $collection->where('trouble_rules_name','like','%'.$param['trouble_rules_name'].'%');
        //创建时间
        if ($param['start_date'] && empty($param['end_date'])) {
            $param['end_date'] = date('Y-m-d H:i:s');
        }
        if (!empty($param['start_date']) && !empty($param['end_date'])) {
            $collection->whereBetween('created_at',[$param['start_date'],$param['end_date']]);
        }
        //表单列排列顺序：最新创建的规则，排列在前面； id排序一个意思
        if ($limit) {
            $result ['count'] = $collection->count();
            $result ['data'] = $collection->orderBy('id','desc')->skip(($offset - 1) * $limit)->take($limit)->get(['id','trouble_type_id','opening_status','trouble_rules_name','created_at','updated_at'])->toArray();
            return $result;
        } else {
            return $collection->orderBy('id')->get()->toArray();
        }
    }

    /**
     * @param $params
     * @return \Illuminate\Database\Eloquent\Collection|Model|mixed|null|static|static[]
     * Note: 获取订单问题数据
     * Data: 2019/3/23 9:06
     * Author: zt7785
     */
    public static function getOrderTroublebyOpt($params,$option = [])
    {
        $collection =  self::with('RulesOrderTroubleType');
        $collection->where('is_deleted',self::UN_DELETED);
        //关联状态下的条件
        $collection->with(['RulesTroubleCondition'=>function ($query) {
            $query->where('is_used',RulesTroubleCondition::STATUS_OPENING);
        }]);
        if ($option) {
            foreach ($option as $k=>$v) {
                $collection->where($k,$v);
            }
        }
        if ($params ['field'] == 'id') {
            return $collection->find($params['value']);
        } else {
            $collection->where($params['field'],$params['value']);
            return $collection->get();
        }
    }

    /**
     * @param $param
     * @param RulesOrderTrouble $trouble
     * Note: 新增 编辑
     * Data: 2019/3/13 18:24
     * Author: zt7785
     */
    public static function setPostData($param ,$trouble)
    {
        //1.规则名称
        //2.问题类型
        //3.问题描述
        //4.选择条件 组装成的规则描述
        //5.组装sql
        //6.记录不同条件值
    }

    /**
     * Note: 自测使用
     * Data: 2019/6/25 10:59
     * Author: zt7785
     */
    public static function orderTroubleFilterTest()
    {
        //1.获取所有规则配置
        $param['opening_status'] = self::STATUS_OPENING;
        $param['trouble_rules_name'] = $param['start_date'] = $param['user_id'] = $param['end_date'] = '';
        $param ['trouble_rules_name'] = 'dsfcd';
        $troublesDatas = self::getTroublesDatas($param);
//        $collection = self::with('RulesOrderTroubleType');
//        $collection->where('is_deleted',self::UN_DELETED);
//        $collection->where('opening_status',self::STATUS_OPENING);
//        $collection->with(['RulesTroubleCondition'=>function ($query) {
//            $query->where('is_used',RulesTroubleCondition::STATUS_OPENING);
//        }]);
//        $troublesDatas = $collection->where('id','>','51')->get()->toArray();
        DB::beginTransaction();
        try {
            foreach ($troublesDatas as $troublesData) {
                //联表 关联字段 初始化
                $tables = $relevance = '';
                //关联模型数组
                $relatedArr = [];
                //2.规则条件逻辑
                if ($troublesData['rules_trouble_condition']) {
                    $relatedArr = array_column($troublesData['rules_trouble_condition'], 'related');
                    $relatedArrCount = count($relatedArr);
                    $sql = '';
                    foreach ($troublesData['rules_trouble_condition'] as $rules_trouble_condition_key => $rules_trouble_condition_val) {
                        if ($rules_trouble_condition_key == ($relatedArrCount - 1)) {
                            $sql .= $rules_trouble_condition_val ['condition_sql'] . ' ';
                        } else {
                            $sql .= $rules_trouble_condition_val ['condition_sql'] . ' AND ';
                        }
                    }
                    //模型权重逻辑
                    if (in_array('Orders', $relatedArr)) {
                        $tables = 'orders';
                        $relevance = ' WHERE ';
                    }

                    if (in_array('OrdersProducts', $relatedArr)) {
                        $tables = 'orders , orders_products ';
                        $relevance = ' WHERE orders.id = orders_products.order_id AND';
                    }

                    if (in_array('Goods', $relatedArr)) {
                        $tables = 'orders ,orders_products , goods ';
                        $relevance = ' WHERE orders.id = orders_products.order_id AND orders_products.goods_id = goods.id AND ';
                    }
                    //上级id 可能是子账号创建的规则
                    $user_id = $troublesData['user_id'];
                    if (!empty($tables)) {
                        self::orderTroubleLogic($sql, $user_id, $tables, $relevance, $troublesData);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            echo '订单问题规则任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
            $exception_data = [
                'start_time'                => date('Y-m-d H:i:s'),
                'msg'                       => '失败信息：' . $e->getMessage(),
                'line'                      => '失败行数：' . $e->getLine(),
                'file'                      => '失败文件：' . $e->getFile(),
            ];
            $exception ['path'] = __FUNCTION__;
            LogHelper::setExceptionLog($exception_data,$exception ['path']);
            $exception ['type'] = 'task';
            $dingPushData ['task'] = '订单问题规则任务';
            $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
            DingRobotWarn::robot($exception,$dingPushData);
            LogHelper::info($exception_data,null,$exception ['type']);
        }
    }
    /**
     * Note: 订单问题规则逻辑定时任务使用
     * Data: 2019/3/15 15:18
     * Author: zt7785
     */
    public static function orderTroubleFilter()
    {
        //1.获取所有规则配置
        $param['opening_status'] = self::STATUS_OPENING;
        $param['trouble_rules_name'] = $param['start_date'] = $param['user_id'] = $param['end_date'] = '';
//        $param ['trouble_rules_name'] = '区间邮编测试';
        $troublesDatas = self::getTroublesDatas($param);
        DB::beginTransaction();
        try {
            foreach ($troublesDatas as $troublesData) {
                //联表 关联字段 初始化
                $tables = $relevance = '';
                //关联模型数组
                $relatedArr = [];
                //2.规则条件逻辑
                if ($troublesData['rules_trouble_condition']) {
                    $relatedArr = array_column($troublesData['rules_trouble_condition'], 'related');
                    $relatedArrCount = count($relatedArr);
                    $sql = '';
                    foreach ($troublesData['rules_trouble_condition'] as $rules_trouble_condition_key => $rules_trouble_condition_val) {
                        if ($rules_trouble_condition_key == ($relatedArrCount - 1)) {
                            $sql .= $rules_trouble_condition_val ['condition_sql'] . ' ';
                        } else {
                            $sql .= $rules_trouble_condition_val ['condition_sql'] . ' AND ';
                        }
                    }
                    //模型权重逻辑
                    if (in_array('Orders', $relatedArr)) {
                        $tables = 'orders';
                        $relevance = ' WHERE ';
                    }

                    if (in_array('OrdersProducts', $relatedArr)) {
                        $tables = 'orders , orders_products ';
                        $relevance = ' WHERE orders.id = orders_products.order_id AND';
                    }

                    if (in_array('Goods', $relatedArr)) {
                        $tables = 'orders ,orders_products , goods ';
                        $relevance = ' WHERE orders.id = orders_products.order_id AND orders_products.goods_id = goods.id AND ';
                    }
                    //上级id 可能是子账号创建的规则
                    $user_id = $troublesData['user_id'];
                    if (!empty($tables)) {
                        self::orderTroubleLogic($sql, $user_id, $tables, $relevance, $troublesData);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

                echo '订单问题规则任务发生错误!失败信息: '.$e->getMessage() . "\r\n";
                $exception_data = [
                    'start_time'                => date('Y-m-d H:i:s'),
                    'msg'                       => '失败信息：' . $e->getMessage(),
                    'line'                      => '失败行数：' . $e->getLine(),
                    'file'                      => '失败文件：' . $e->getFile(),
                ];
                $exception ['path'] = __FUNCTION__;
                LogHelper::setExceptionLog($exception_data,$exception ['path']);
                $exception ['type'] = 'task';
                $dingPushData ['task'] = '订单问题规则任务';
                $dingPushData ['message'] = $exception_data ['file']."\n\n".$exception_data ['line']."\n\n".$exception_data ['msg'];
                DingRobotWarn::robot($exception,$dingPushData);
                LogHelper::info($exception_data,null,$exception ['type']);
        }
    }

    /**
     * @param $sql sql
     * @param $user_id 客户id
     * @param $tables 联表
     * @param $relevance 联表条件
     * @param $ruleData 规则配置信息
     * Note: 问题订单查询过滤
     * Data: 2019/3/15 15:07
     * Author: zt7785
     */
    public static function orderTroubleLogic($sql,$user_id,$tables,$relevance,$ruleData)
    {
        //待获取字段 默认inner join 会有重复值 去重
        $selectFiled = 'DISTINCT orders.id';
        //待查询sql
        $querySql = str_replace('WHERE','',$sql);
        $permissionSql = 'AND orders.created_man = '.$user_id .' AND orders.picking_status = '. Orders::ORDER_PICKING_STATUS_UNMATCH .' AND orders.status = '. Orders::ORDER_STATUS_UNFINISH ;
        $query_sql = 'select '.$selectFiled.' from '.$tables.$relevance.$querySql.$permissionSql;
        if (!is_bool(strpos($query_sql,'AND AND'))) {
            $query_sql = str_replace('AND AND',' AND ',$query_sql);
        }
        $results = DB::select($query_sql);
//        dump($results,$ruleData['id'],'select '.$selectFiled.' from '.$tables.$relevance.$querySql.$permissionSql);
//        dd(1) ;
//        return ;
        //问题订单写问题表
        if ($results) {
            $resultsCount = count($results);
            echo "订单问题规则名称: ".$ruleData['trouble_rules_name'] ." 规则ID:".$ruleData['id']." 共有 ".$resultsCount." 条订单检测到异常 \r\n";
            //写问题记录
            $current_time = date('Y-m-d H:i:s');
            //感觉要分片 有隐患
            $troublesLen = ceil($resultsCount/500);
            $troublesTempArr = [];
            for ($len = 0;$len < $troublesLen; $len++) {
                $troublesRecordData = [];
                $orderLogData = [];
                $troublesTempArr = array_slice($results,500*$len, 500);
                $troublesTempArrIds = array_column($troublesTempArr,'id');
                //1 先查出该片订单 有同类型问题 未处理的订单
                $troublesExistIds = OrdersTroublesRecord::where('trouble_rule_id',$ruleData['id'])->where('dispose_status',OrdersTroublesRecord::STATUS_DISPOSING)->whereIn('order_id',$troublesTempArrIds)->get(['id','order_id'])->toArray();

                //2 避免重复插入 剔除 array_values初始化索引 array_diff 将存在的订单id 从该片中剔除
                $diffOrderIds = array_values(array_diff($troublesTempArrIds,array_column($troublesExistIds,'order_id')));
                foreach ($diffOrderIds as $resultKey => $result) {
                    //如果问题表有该订单该规则数据怎么办?
                    $troublesRecordData [$resultKey]['order_id'] = $result;
                    $troublesRecordData [$resultKey]['created_at'] = $troublesRecordData [$resultKey]['updated_at'] = $current_time;
                    $troublesRecordData [$resultKey]['created_man'] = '1' ;//默认系统操作
                    $troublesRecordData [$resultKey]['trouble_rule_id'] = $ruleData['id'] ;
                    $troublesRecordData [$resultKey]['trouble_type_id'] = $ruleData['rules_order_trouble_type']['id'] ;
                    $troublesRecordData [$resultKey]['question_type'] = OrdersTroublesRecord::QUESTION_TYPE_ORDERS;
                    $troublesRecordData [$resultKey]['trouble_name'] = $ruleData['trouble_rules_name'];
                    $troublesRecordData [$resultKey]['trouble_desc'] = $ruleData['trouble_desc'];
                    $troublesRecordData [$resultKey]['dispose_status'] = OrdersTroublesRecord::STATUS_DISPOSING;
                    //日志数据
                    $orderLogData [$resultKey] ['created_man'] = 1;
                    $orderLogData [$resultKey] ['order_id'] = $result;
                    $orderLogData [$resultKey] ['behavior_types'] = OrdersLogs::LOGS_ORDERS_TROUBLE;
                    $orderLogData [$resultKey] ['behavior_desc'] = $ruleData['trouble_desc'];
                    $orderLogData [$resultKey] ['behavior_type_desc'] = OrdersLogs::ORDERS_LOGS_TYPE_DESC[$orderLogData [$resultKey] ['behavior_types']];
                    $orderLogData [$resultKey]['created_at'] = $orderLogData [$resultKey]['updated_at'] = $current_time;
                }
                //数据插入  第".($len +1)."组数据\r\n"; 'id','trouble_type_id','opening_status','trouble_rules_name','created_at','updated_at'
                if (!empty($troublesRecordData)) {
                    OrdersTroublesRecord::insert($troublesRecordData);
                    //写日志
                    OrdersLogs::insert ($orderLogData);
                }
            }
        } else {
            echo "订单问题规则名称: ".$ruleData['trouble_rules_name'] ." 规则ID:".$ruleData['id']." 共有 0 条订单检测到异常 \r\n";
        }
    }

    /**
     * @param int $id
     * @param $data
     * @return Model
     * Note: 新增更新
     * Data: 2019/3/13 18:41
     * Author: zt7785
     */
    public static function postDatas($id = 0, $data)
    {
        if (!isset($data ['is_deleted'])) {
            $data ['is_deleted'] = self::UN_DELETED;
        }
        return self::updateOrCreate(['id' => $id], $data);
    }
}


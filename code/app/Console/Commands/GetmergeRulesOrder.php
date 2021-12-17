<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 9:38
 */

namespace App\Console\Commands;

use App\Models\Orders;
use App\Common\Common;
use App\Models\OrdersTroublesRecord;
use App\Models\RulesOrderSplitMerge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetmergeRulesOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetmergeRulesOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '合并订单定时任务';
    private $goodsWares = array();
    private $svcCall = null;
    public $cache = null;

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
        set_time_limit(0);
        date_default_timezone_set('PRC');//设置时间为中国时区
        exec("chcp 65001");//设置命令行窗口为中文编码
        echo "合单任务开始\r\n";
        try{
            $plateform = RulesOrderSplitMerge::get();
            if($plateform->isEmpty()) {
                echo "合单规则未开启\r\n";
                return false;
            }
            $plateform = $plateform->toArray();
            foreach($plateform as $platk => $platv) {
                //如果禁用合单规则不予处理
                if($platv['status'] == RulesOrderSplitMerge::STATUS_DIS) {
                    continue;
                }

                //未配货订单 只查询开启指定平台
                $orders = Orders::whereDoesntHave('OrdersTroublesRecord', function($query){
                    $query->where('dispose_status',1);
                })
                    ->where(['picking_status' => Orders::ORDER_PICKING_STATUS_UNMATCH])
                    ->where('status', '=', Orders::ORDER_STATUS_UNFINISH)
                    ->where('user_id',$platv['user_id'])
                    ->where('platforms_id',$platv['type'])
                    ->where('merge_orders_id', '=', '')
                    ->get();

                if ($orders->isEmpty()) {
                    continue;
                }
                //已经合并的订单不需要再合并
                $orders = $orders->toArray();
                $mergeOrderId = array_column($orders, 'merge_orders_id');
                foreach ($mergeOrderId as $key => $val) {
                    if (!empty($val)) {
                        unset($orders[$key]);
                    }
                }

                $unique_arr = $this->getRepeat($orders, [
                    'platforms_id', 'source_shop', 'user_id', 'addressee_name', 'addressee1', 'addressee2', 'addressee_email', 'country_id', 'city', 'mobile_phone', 'phone', 'postal_code', 'province',
                ]);
                //不满足地址相同 店铺相同
                if (empty($unique_arr)) {
                    echo "无可合并订单\r\n";
                    continue;

                }
                foreach ($unique_arr as $key => $val) {
                    $comma = strpos($val, ',');
                    if (is_bool($comma)) {
                        unset($unique_arr[$key]);
                    }
                }
                //入库
                $insertRecordArr = [];
                foreach ($unique_arr as $k => $v) {
                    $troubleArr = explode(',', $v);
                    foreach ($troubleArr as $troubleKey => $troubleValue) {
                        $ordersRecordInfo = OrdersTroublesRecord::where(['order_id' => $troubleValue, 'trouble_type_id' => OrdersTroublesRecord::STATUS_MERGE])->first();
                        //已有记录更新
                        if ($ordersRecordInfo) {
                            $re = OrdersTroublesRecord::where(['order_id' => $troubleValue, 'trouble_type_id' => OrdersTroublesRecord::STATUS_MERGE])->update(['trouble_desc' => $v]);
                            if(!$re) echo '已有合并订单更新失败,订单id为'.$troubleKey."\r\n";
                            continue;
                        }
                        $insertRecordArr[$troubleValue]['created_man'] = 1;
                        $insertRecordArr[$troubleValue]['manage_id'] = 0;
                        $insertRecordArr[$troubleValue]['order_id'] = $troubleValue;
                        $insertRecordArr[$troubleValue]['trouble_name'] = '合并订单';
                        $insertRecordArr[$troubleValue]['trouble_desc'] = $v;
                        $insertRecordArr[$troubleValue]['manage_remark'] = '';
                        $insertRecordArr[$troubleValue]['dispose_status'] = OrdersTroublesRecord::STATUS_DISPOSING;
                        $insertRecordArr[$troubleValue]['created_at'] = date('Y-m-d H:i:s', time());
                        $insertRecordArr[$troubleValue]['updated_at'] = date('Y-m-d H:i:s', time());
                        $insertRecordArr[$troubleValue]['trouble_type_id'] = 4;
                    }
                }
                $result = DB::table('orders_troubles_record')->insert($insertRecordArr);
                if(!$result){
                    echo "订单合并数据入库失败\r\n";
                    return false;
                }

            }
            echo "合单任务结束\r\n";

        } catch (\Exception $e) {
            Common::mongoLog($e,'订单合并数据','入库失败',__FUNCTION__);
            echo $e->getMessage();
            echo $e->getCode();
        }
    }

    /**
     * @author zt7837
     * 导出信息迭代器
     * @param $yield_arr
     * @return \Generator
     */
    public function exportYield($yield_arr)
    {
        for ($i = 0; $i < count($yield_arr); $i++) {
            yield $yield_arr[$i];
        }
    }

    /**
     * @note
     * 指定字段数组去重
     * @since: 2019/5/17
     * @author: zt7837
     * @return: array
     */
    public function getRepeat($arr, $keys)
    {
        $unique_arr = array();
        $order_id = array();
        $order_id_arr = array();
        foreach ($arr as $k => $v) {
            $str = "";
            foreach ($keys as $a => $b) {
                $str .= "{$v[$b]},";
            }
            if (!in_array($str, $unique_arr)) {
                $unique_arr[] = $str;
                $order_id[$v['id']] = $str;
                $order_id_arr[$v['id']] = $v['id'];
            } else {
                $key = array_search($str, $order_id);
                $order_id_arr[$key] = $order_id_arr[$key] . ',' . $v['id'];
            }

        }
        return $order_id_arr;
    }

}
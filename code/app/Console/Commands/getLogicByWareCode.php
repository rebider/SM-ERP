<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:57
 */

namespace App\Console\Commands;

use App\Common\Common;
use App\Models\SettingLogistics;
use App\Models\SettingWarehouse;
use App\Models\ShippingMethodJapan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class getLogicByWareCode extends Command
{
    //todo
    //未部署
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getLogicByWareCode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '速贸同步物流信息';
    private $goodsWares = array();
    private $svcCall = null;
    public $cache = null;

    const SMWAREHOUSE = 1;

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
     * Note: 物流数据写入
     * Data: 2018/7/24 13:54
     * Author: zt7837
     */
    private function addLogistics()
    {
        echo "开始物流录入逻辑\r\n";
        DB::beginTransaction();
        try {
            $param = config('common.logistic_api');

            $warehouseInfo = SettingWarehouse::join('warehouse_secretkey','setting_warehouse.user_id','warehouse_secretkey.user_id')->where(['disable' => SettingWarehouse::ON ,'type' => SettingWarehouse::SM_TYPE])->get();
            $warehouseInfoArr = (!$warehouseInfo->isEmpty()) ? $warehouseInfo->toArray() : '';

            $finalLogicticsArr = [];
            if (empty($warehouseInfoArr)) {
                echo '速贸物流同步异常,速贸仓库为空!';
//                info('速贸物流同步异常,速贸仓库为空!' . date('Y-m-d H:i:s', time()));
                return;
            }
            $common = new Common();
            $final_key = 0;
            foreach ($warehouseInfoArr as $k => $v) {
                $param['warehouseCode'] = $v['warehouse_code'];
                $accout['appToken'] = $v['appToken'];
                $accout['appKey'] = $v['appKey'];
                $wareLogicArr = $common->sendWarehouse('getShippingMethod', $param,$accout);
                $removeWareLogicArr = $this->assoc_unique($wareLogicArr['data'], 'name');
                foreach ($removeWareLogicArr as $key => $val) {
                    //第一组物流肯定不重复
                    if (!empty($finalLogicticsArr) && $k != 0) {
                        //这里需要判断搜到的物流key是否属于同一个用户.
                        //low一点用foreach
                        foreach($finalLogicticsArr as $fink => $finv) {
                            if(($v['user_id'] == $finv['user_id']) && ($val['code'] == $finv['code'])) {
                                $finalLogicticsArr[$fink]['warehouse_name'] .= ',' . $v['warehouse_name'];
                                $finalLogicticsArr[$fink]['warehouse_code'] .= ',' . $v['warehouse_code'];
                                continue 2;
                            }
                        }

                    }
                    $finalLogicticsArr[$final_key]['code'] = $val['code'];
                    $finalLogicticsArr[$final_key]['name'] = $val['name'];
                    $finalLogicticsArr[$final_key]['warehouse_name'] = $v['warehouse_name'];
                    $finalLogicticsArr[$final_key]['warehouse_code'] = $v['warehouse_code'];
                    $finalLogicticsArr[$final_key]['user_id'] = $v['user_id'];
                    $final_key++;
                }
                //每次删除原来用户的物流
                $seting_wh['user_id'] = $v['user_id'];
                ShippingMethodJapan::where($seting_wh)->delete();
            }
            if (!empty($finalLogicticsArr)) {
                $re = DB::table('shipping_method_japan')->insert($finalLogicticsArr);
                if (!$re) {
                    echo '速贸物流入库异常!';
                    DB::rollback();
//                    info('速贸物流入库异常!' . date('Y-m-d H:i:s', time()));
                    return false;
                } else {
                    echo '物流录入完成共'.count($finalLogicticsArr).'条数据';
//                    info('速贸物流同步完成!' . date('Y-m-d H:i:s', time()));

                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage();
            echo $e->getCode();
            Common::mongoLog($e,'同步速贸物流','失败',__FUNCTION__);
        }
    }

    /**
     * @note
     * 去重
     * @since: 2019/5/27
     * @author: zt7837
     * @return: array
     */
    public function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr);
        return $arr;
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
        $this->addLogistics();//物流入录
    }
}
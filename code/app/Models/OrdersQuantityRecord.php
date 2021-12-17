<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrdersQuantityRecord
 * Notes: 订单量模型
 * @package App\Models
 * Data: 2019/4/19 9:24
 * Author: zt7785
 */
class OrdersQuantityRecord extends Model
{
    protected $table = 'orders_quantity_record';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","user_id","shop_id","platforms_id","quantity","record_times","created_at","updated_at"];


    /**
     * @param $saleInfo
     * @param $record_time
     * @return Model
     * Note: 匹配成功 写订单量逻辑
     * Data: 2019/4/19 10:18
     * Author: zt7785
     */
    public static function orderQuantityLogics($saleInfo ,$record_time )
    {
        //对应时间搓 是否有订单记录
        $recordInfo = self::where([
            ['user_id',$saleInfo['user_id']],
            ['shop_id',$saleInfo['shop_id']],
            ['platforms_id',$saleInfo['platforms_id']],
            ['record_times',$record_time],
        ])->first(['id', 'quantity']);
        if (empty($recordInfo)) {
            $quantityData ['user_id'] = $saleInfo ['user_id'];
            $quantityData ['shop_id'] = $saleInfo ['shop_id'];
            $quantityData ['platforms_id'] = $saleInfo ['platforms_id'];
            $quantityData ['quantity'] = 1;
            $quantityData ['record_times'] = $record_time;
            return self::postDatas(0,$quantityData);
        } else {
            $quantityData ['quantity'] = $recordInfo['quantity'] + 1;
            return self::postDatas($recordInfo['id'],$quantityData);
        }
    }

    /**
     * @param $saleInfo 客户id 店铺id 平台id
     * @param $created_at 平台订单创建时间
     * @return bool|Model
     * Note: 取消订单 订单量逻辑
     * Data: 2019/4/19 10:17
     * Author: zt7785
     */
    public static function orderQuantityCancelLogics($saleInfo ,$created_at )
    {
        //对应时间搓 是否有订单记录
        $created = explode(' ',$created_at);
        $recordInfo = self::where([
            ['user_id',$saleInfo['user_id']],
            ['shop_id',$saleInfo['shop_id']],
            ['platforms_id',$saleInfo['platforms_id']],
        ])->whereDate('created_at',$created[0])->first();
        if (empty($recordInfo)) {
            return true;
        } else {
            if (empty($recordInfo ['quantity']))
            {
                return true;
            }
            $quantityData ['quantity'] = $recordInfo['quantity'] - 1;
            return self::postDatas($recordInfo['id'],$quantityData);
        }
    }

    public static function postDatas($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    public static function getOrderSummary($user_id)
    {
        $sqlDate = [
            'year' => ['startPoint' => 6, 'length' => 2, 'name' => 'month', 'yearLimit' => date('Y-01-01 00:00:00')],
            'month' => ['startPoint' => 9, 'length' => 2, 'name' => 'day', 'monthLimit' => date('Y-m-01 00:00:00'), ],
            'week' => ['startPoint' => 9, 'length' => 2, 'name' => 'day',
                'limitedStart' => date('Y-m-d 00:00:00', strtotime('-7days')),
                'limitedEnd' => date('Y-m-d 00:00:00')
            ]
        ];
        $orderSummary = [];
        $weeklySql = "SELECT SUM(quantity) as `quantity`, SUBSTR(created_at, {$sqlDate['week']['startPoint']}, {$sqlDate['week']['length']}) 
                        AS '{$sqlDate['week']['name']}' FROM orders_quantity_record WHERE created_at >= '{$sqlDate['week']['limitedStart']}'
                        AND created_at < '{$sqlDate['week']['limitedEnd']}' AND user_id = {$user_id} GROUP BY `{$sqlDate['week']['name']}`";
        $monthlySql = "SELECT SUM(quantity) as `quantity`, SUBSTR(created_at, {$sqlDate['month']['startPoint']}, {$sqlDate['month']['length']}) 
                        AS '{$sqlDate['month']['name']}' FROM orders_quantity_record WHERE created_at > '{$sqlDate['month']['monthLimit']}' 
                         AND user_id = {$user_id} GROUP BY `{$sqlDate['week']['name']}`";
        $annuallySql = "SELECT SUM(quantity) as `quantity`, SUBSTR(created_at, {$sqlDate['year']['startPoint']}, {$sqlDate['year']['length']}) 
                        AS '{$sqlDate['year']['name']}' FROM orders_quantity_record WHERE user_id = {$user_id} 
                        AND created_at >= '{$sqlDate['year']['yearLimit']}' AND created_at < '{$sqlDate['month']['monthLimit']}' GROUP BY `{$sqlDate['year']['name']}`";
        $orderSummary['weekly'] = DB::select($weeklySql);
        $orderSummary['monthly'] = DB::select($monthlySql);
        $orderSummary['annually'] = DB::select($annuallySql);
        foreach ($orderSummary as $key => $val) {
            if (empty($val)) {
                continue;
            }
            $orderSummary[$key] = array_map('get_object_vars', $val);
        }
        return $orderSummary;
    }

}

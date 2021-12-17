<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersLogs
 * Notes: 订单日志
 * @package App\Models
 * Data: 2019/3/7 12:11
 * Author: zt7785
 */
class OrdersLogs extends Model
{
    protected $table = 'orders_logs';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','order_id','behavior_types','behavior_type_desc','behavior_desc','created_at','updated_at'];

    /**
     * @var 订单日志创建订单
     */
    const LOGS_ORDERS_CREATED = 1;
    /**
     * @var 订单日志编辑订单
     */
    const LOGS_ORDERS_EDITED = 2;
    /**
     * @var 订单日志拦截订单
     */
    const LOGS_ORDERS_INTERCEPTING = 3;
    /**
     * @var 订单日志拦截订单失败
     */
    const LOGS_ORDERS_INTERCEPT_FAILED = 4;
    /**
     * @var 订单日志拦截订单成功
     */
    const LOGS_ORDERS_INTERCEPT_SUCC = 5;
    /**
     * @var 订单日志取消订单
     */
    const LOGS_ORDERS_CANCEL = 6;
    /**
     * @var 订单日志部分商品退款
     */
    const LOGS_ORDERS_PART_PRODUCT_REFUNDING = 7;
    /**
     * @var 订单日志部分商品退款成功
     */
    const LOGS_ORDERS_PART_PRODUCT_REFUNDED = 8;
    /**
     * @var 订单日志部分商品退款失败
     */
    const LOGS_ORDERS_PART_PRODUCT_REFUND_FAILED = 9;
    /**
     * @var 订单日志部分配货
     */
    const LOGS_ORDERS_PART_PRODUCT_PICKING = 10;
    /**
     * @var 订单日志部分配货成功
     */
    const LOGS_ORDERS_PRODUCT_PICKED = 11;
    /**
     * @var 订单日志部分发货
     */
    const LOGS_ORDERS_PART_PRODUCT_DELIVERING = 12;
    /**
     * @var 订单日志发货成功
     */
    const LOGS_ORDERS_PRODUCT_DELIVERED = 13;
    /**
     * @var 订单日志创建售后单
     */
    const LOGS_ORDERS_AFTER_SALES_CREATION = 14;
    /**
     * @var 订单日志订单完成
     */
    const LOGS_ORDERS_FINISHED = 15;
    /**
     * @var 订单日志订单作废
     */
    const LOGS_ORDERS_OBSOLETED = 16;

    /**
     * @var 订单日志订单问题规则匹配成功
     */
    const LOGS_ORDERS_TROUBLE = 17;


    /**
     * @var 订单日志常规描述
     */
    const ORDERS_LOGS_DESC = [
        self::LOGS_ORDERS_CREATED => '创建订单',
        self::LOGS_ORDERS_INTERCEPTING => '发起拦截',
        self::LOGS_ORDERS_INTERCEPT_FAILED => '拦截失败',
        self::LOGS_ORDERS_INTERCEPT_SUCC => '拦截成功',
        self::LOGS_ORDERS_PART_PRODUCT_PICKING => '部分配货',
        self::LOGS_ORDERS_PRODUCT_PICKED => '完全配货',
        self::LOGS_ORDERS_PART_PRODUCT_DELIVERING => '部分发货',
        self::LOGS_ORDERS_PRODUCT_DELIVERED => '完全发货',
        self::LOGS_ORDERS_CANCEL => '取消订单',
        self::LOGS_ORDERS_PART_PRODUCT_REFUND_FAILED => '部分退款失败',
        self::LOGS_ORDERS_AFTER_SALES_CREATION => '创建售后单',
    ];

    /**
     * @var 订单日志操作描述
     */
    const ORDERS_LOGS_TYPE_DESC = [
        self::LOGS_ORDERS_CREATED => '创建订单',
        self::LOGS_ORDERS_EDITED => '编辑订单',
        self::LOGS_ORDERS_INTERCEPTING => '拦截订单',
        self::LOGS_ORDERS_INTERCEPT_FAILED => '拦截订单',
        self::LOGS_ORDERS_INTERCEPT_SUCC => '拦截订单',
        self::LOGS_ORDERS_CANCEL => '取消订单',
        self::LOGS_ORDERS_PART_PRODUCT_REFUNDING => '部分退款',
        self::LOGS_ORDERS_PART_PRODUCT_REFUNDED => '部分退款',
        self::LOGS_ORDERS_PART_PRODUCT_REFUND_FAILED => '部分退款',
        self::LOGS_ORDERS_PART_PRODUCT_PICKING => '配货',
        self::LOGS_ORDERS_PRODUCT_PICKED => '配货',
        self::LOGS_ORDERS_PART_PRODUCT_DELIVERING => '发货',
        self::LOGS_ORDERS_PRODUCT_DELIVERED => '发货',
        self::LOGS_ORDERS_AFTER_SALES_CREATION => '创建售后单',
        self::LOGS_ORDERS_FINISHED => '订单完成',
        self::LOGS_ORDERS_OBSOLETED => '订单作废',
        self::LOGS_ORDERS_TROUBLE => '订单问题',
    ];
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function Orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    /**
     * @param $param
     * @return array
     * Note: 获取订单日志
     * Data: 2019/3/23 11:16
     * Author: zt7785
     */
    public static function getOrderLogsByCondition($param)
    {
        $collection = self::select('id','created_man','order_id','behavior_types','behavior_desc','behavior_type_desc');
        if (isset($param ['order_id']) && !empty($param ['order_id'])) {
            $collection->where('order_id',$param ['order_id']);
        }
        if (isset($param ['behavior_types']) && !empty($param ['behavior_types'])) {
            $collection->where('behavior_types',$param ['behavior_types']);
        }
        return $collection->get()->toArray();
    }

    /**
     * @param int $id
     * @param $data
     * @return Model
     * Note: updateOrCreate 返回模型
     * Data: 2019/3/12 19:07
     * Author: zt7785
     */
    public static function postDatas($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }

    /**
     * @param $order_id
     * @param $user_id
     * @param $order_type
     * Note: 常规日志
     * Data: 2019/5/31 10:01
     * Author: zt7785
     */
    public static function standardOrderLogs($order_id, $user_id,$order_type)
    {
        //日志
        $orderLogsData ['created_man'] = $user_id;
        $orderLogsData ['order_id'] = $order_id;
        $orderLogsData ['behavior_types'] = $order_type;
        //描述
        $orderLogsData ['behavior_desc'] = self::ORDERS_LOGS_DESC[$orderLogsData ['behavior_types']];
        //操作
        $orderLogsData ['behavior_type_desc'] = self::ORDERS_LOGS_TYPE_DESC[$orderLogsData ['behavior_types']];
        $orderLogsData ['updated_at'] = $orderLogsData ['created_at'] = date('Y-m-d H:i:s');
        return self::postDatas(0,$orderLogsData);
    }
}

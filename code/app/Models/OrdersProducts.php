<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

class OrdersProducts extends Model
{
    protected $table = 'orders_products';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ["id","created_man","user_id","order_id","goods_id","order_type","product_name","sku","currency","buy_number","already_stocked_number","cargo_distribution_number","delivery_number","partial_refund_number","weight","univalence","rate","RMB","created_at","updated_at","is_deleted",'AmazonOrderItemCode','aftersale_refund_number'];


    /**
     * @var CW_REP订单
     */
    const ORDERS_CWERP = 1;

    /**
     * @var 原始订单亚马逊
     */
    const ORDERS_ORIG_AMAZON = 2;

    /**
     * @var 原始订单乐天
     */
    const ORDERS_ORIG_RAKUTEN = 3;


    /**
     * @var 订单商品删除状态已删除
     */
    const ORDERS_PRODUCT_DELETED = 1;

    /**
     * @var 订单商品删除状态未删除
     */
    const ORDERS_PRODUCT_UNDELETED = 0;



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

    public function Goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    /**
     * @note
     * 更新已发货数量
     * @since: 2019/5/31
     * @author: zt7837
     * @return: array
     */
    public static function updateDeliveryNumber($goods_id,$order_id,$number){
        $orderAfterObj = OrdersProducts::where(['goods_id' => $goods_id,'order_id' => $order_id,'is_deleted' => self::ORDERS_PRODUCT_UNDELETED])->first();
        $afterNumber = $orderAfterObj->delivery_number - $number;
        $orderAfterObj->delivery_number = $afterNumber;
        return $orderAfterObj->save();
    }

}

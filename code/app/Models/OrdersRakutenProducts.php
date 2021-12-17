<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersRakutenProducts extends Model
{
    //
    protected $table = 'orders_rakuten_products';

    public $timestamps = true;

    public $primaryKey = 'id';

    /**
     * @param $order_id
     * @param $goods_id
     * @return array
     * Note: 根据订单id 商品id 获取乐天商品源价格
     * Data: 2019/4/11 17:28
     * Author: zt7785
     */
    public static function getProductInfoByOrderidGoodsid($order_id,$goods_id)
    {
        return self::where('goods_id',$goods_id)->where('rakuten_order_id',$order_id)->first(['id','itemId','itemName','itemNumber','price'])->toArray();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersAmazonProducts extends Model
{
    protected $table = 'orders_amazon_products';

    public $timestamps = true;

    public $primaryKey = 'id';


    /**
     * @param $order_id
     * @param $goods_id
     * @return array
     * Note: 根据订单id 商品id 获取亚马逊商品源价格
     * Data: 2019/4/11 17:28
     * Author: zt7785
     */
    public static function getProductInfoByOrderidGoodsid($order_id,$goods_id)
    {
        return self::where('goods_id',$goods_id)->where('amazon_order_id',$order_id)->first(['id','ASIN','SellerSKU','Title','ItemPrice','unit_discount_price','ShippingPrice','shipping_fee'])->toArray();
    }
}

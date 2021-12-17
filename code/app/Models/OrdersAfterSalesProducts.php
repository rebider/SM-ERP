<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class OrdersAfterSalesProducts
 * Notes: 售后单商品明细表
 * @package App\Models
 * Data: 2019/3/7 14:26
 * Author: zt7785
 */
class OrdersAfterSalesProducts extends Model
{
    protected $table = 'orders_after_sales_products';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','after_sale_id','product_name','sku','goods_id','attribute','number','univalence','rate','currency','created_at','updated_at'];

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
     * Note: 订单售后表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function OrdersAfterSales()
    {
        return $this->belongsTo(OrdersAfterSales::class, 'after_sale_id', 'id');
    }

    public function goods(){
        return $this->hasOne(Goods::class,'id','goods_id');
    }

    /**
     * @note 退款单
     * @since: 2019/4/17
     * @author: zt7837
     * @return: array
     */
    public static function createAfterSaleProducts($afterOrderProduct,$model){
        $model->created_man = $afterOrderProduct['created_man'];
        $model->after_sale_id = $afterOrderProduct['after_sale_id'];
        $model->product_name = $afterOrderProduct['product_name'];
        $model->sku = $afterOrderProduct['sku'];
        $model->attribute = $afterOrderProduct['attribute'];
        $model->number = $afterOrderProduct['number'];
        $model->univalence = $afterOrderProduct['univalence'];
        $model->rate = $afterOrderProduct['rate'];
        $model->currency = $afterOrderProduct['currency'];
        $model->goods_id = $afterOrderProduct['goods_id'];
        return $model->save() ? $model : false;
    }


}

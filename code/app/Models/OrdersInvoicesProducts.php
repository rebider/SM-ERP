<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

class OrdersInvoicesProducts extends Model
{
    const UN_STATUS = 0;//未完成
    const ON_STATUS = 1;//已完成

    protected $table = 'orders_invoice_products';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','order_id','invoice_id','product_id','product_name','sku','attribute','number','univalence','currency','rate','created_at','updated_at','AmazonOrderItemCode'];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 订单表
     * Data: 2019/3/7 14:14
     * Author: zt7785
     */
    public function OrdersInvoices()
    {
        return $this->belongsTo(OrdersInvoices::class, 'invoice_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Note: 商品表
     * Data: 2019/3/26 20:14
     * Author: zt8076
     */
    public function Goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

}

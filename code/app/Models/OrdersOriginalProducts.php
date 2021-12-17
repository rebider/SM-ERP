<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersOriginalProducts extends Model
{
    protected $table = 'orders_original_products';
    public $timestamps = true;
    public $primaryKey = 'id';
    public $fillable = ['original_order_id', 'created_man', 'user_id', 'platform_id',
        'sku', 'goods_id', 'price', 'quantity', 'created_at', 'updated_at', 'deleted_at','RMB','good_img','rate','good_name','AmazonOrderItemCode'];

    public function OrdersOriginal()
    {
        return $this->belongsTo(OrdersOriginal::class, 'original_order_id', 'id');
    }
}

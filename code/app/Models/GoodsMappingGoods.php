<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsMappingGoods extends Model
{
    protected $table = 'goods_mapping_goods';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'user_id' ,'created_man','goods_mapping_id','goods_id', 'goods_number', 'goods_sku', 'created_at', 'updated_at'];


    public function Goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    public function Mapping()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'id');
    }

    /**
     * @param $mappingId
     * @return array
     * Note: 订单商品映射查询 勿动
     * Data: 2019/6/10 17:18
     * Author: zt7785
     */
    public static function getGoodsIdByMappingid($mappingId)
    {
        $result =  self::with('Mapping','Goods')->where('goods_mapping_id',$mappingId)->first(['goods_id']);
        if (empty($result)) {
            return [];
        } else {
            return $result->toArray();
        }
    }
}

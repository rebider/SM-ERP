<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsMapping extends Model
{
    const MAPPING_ON = 0;//未映射

    const MAPPING_YES = 1;//已映射

    protected $table = 'goods_mapping';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'user_id', 'created_man','platforms_id', 'setting_shops_id', 'itemURL', 'item_number', 'seller_sku', 'asin', 'upc', 'status', 'type', 'created_at', 'updated_at'];

    /**
     * @return $this
     * Note: 平台模型
     * Data: 2019/6/5 10:16
     * Author: zt8067
     */
    public function platforms(){
        return $this->belongsTo(Platforms::class,'platform_id','id')->select(['id','name_CN','name_EN']);
    }

    /**
     * @return $this
     * Note: 商铺模型
     * Data: 2019/6/5 10:38
     * Author: zt8067
     */
    public function shop(){
        return $this->belongsTo(SettingShops::class,'setting_shops_id','id')->select(['id','shop_name']);
    }

    /**
     * @return $this
     * Note: 映射商品模型
     * Data: 2019/6/5 10:42
     * Author: zt8067
     */
    public function mapping_goods(){
        return $this->belongsToMany(Goods::class ,'goods_mapping_goods', 'goods_mapping_id','goods_id')->select(['goods_mapping_goods.goods_id as id','goods_mapping_goods.id as gmg_id','goods_number','goods_sku']);
    }

    /**
     * @param $user_id
     * @param $sku
     * @param $plat_id
     * @return array
     * Note: 获取客户维护的商品映射信息
     * Data: 2019/6/29 9:29
     * Author: zt7785
     */
    public static function getMappingInfoByUseridSku($user_id,$sku,$plat_id)
    {
        $collection =  self::where('user_id',$user_id)->where('platform_id',$plat_id)->where('status',self::MAPPING_YES);
        if ($plat_id == 1) {
            if (is_string($sku)) {
                $collection->where('seller_sku',$sku);
            } else if (is_array($sku)) {
                $collection->whereIn('seller_sku',$sku);
            }
        } else if ($plat_id == 2) {
            if (is_string($sku)) {
                $collection->where('itemURL',$sku);
            } else if (is_array($sku)) {
                $collection->whereIn('itemURL',$sku);
            }
        } else {
            return [];
        }
        return $collection->get(['id','seller_sku','itemURL'])->toArray();
    }
}

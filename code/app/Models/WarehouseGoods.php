<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseGoods
 * @description: 仓库商品表
 * @author: zt7927
 * @data: 2019/3/19 15:19
 * @package App\Models
 */
class WarehouseGoods extends Model
{
    protected $table = 'warehouse_goods';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'goods_id', 'currency_id', 'created_man', 'sku', 'goods_name', 'isset_battery', 'bases', 'goods_height', 'goods_width', 'goods_length', 'goods_weight', 'product_title', 'ch_name', 'eh_name', 'price', 'warehouse_category1', 'warehouse_category2', 'warehouse_category3', 'sync', 'sync_time', 'reason', 'purchase_inventory', 'in_transit_inventory', 'drop_shipping', 'available_in_stock', 'available_in_stock', 'created_at', 'updated_at'];

    const STATUS = 1;

    const BATTERY = 1;

    const UNBATTERY = 0;
    /*
     * @var 同步中
     */
    const SYNCING = 2;

    /**
     * @description 查询仓库商品信息
     * @author zt7927
     * @data 2019/3/25 16:48
     * @param $id
     * @param $warehouse_id
     * @return mixed
     */
    public function getWarehouseGoodsById($id)
    {
        if (is_numeric($id) && $id>0){
            return self::where('id', $id)->first();
        }
        return false;
    }
    /**
     * @return $this
     * Note: 关联仓库与商品模型
     * Data: 2019/4/8 17:00
     * Author: zt8067
     */
    public function warehouseHasGoods()
    {
        return $this->belongsToMany(SettingWarehouse::class, 'warehouse_type_goods', 'goods_id', 'setting_warehouse_id');
    }

    public static function addWareHouseData($insert,$id = 0){
        return self::updateOrCreate(['goods_id'=>$id],$insert);
    }

    /**
     * @description 一级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category1()
    {
        return  $this->hasOne(Category::class ,'category_id' ,'warehouse_category1')->where('type' ,1) ;
    }

    /**
     * @description 二级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category2()
    {
        return $this->hasOne(Category::class ,'category_id' ,'warehouse_category2')->where('type' ,1) ;
    }

    /**
     * @description 3级分类
     * @author zt6650
     * @creteTime 2019/3/28 17:35
     */
    public function category3()
    {
        return $this->hasOne(Category::class ,'category_id' ,'warehouse_category3')->where('type' ,1) ;
    }

}

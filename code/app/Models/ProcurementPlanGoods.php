<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcurementPlanGoods
 * @description: 采购计划与商品关联表
 * @author: zt7927
 * @data: 2019/3/18 14:44
 * @package App\Models
 */
class ProcurementPlanGoods extends Model
{
    protected $table = 'procurement_plan_goods';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'created_man', 'procurement_plan_id', 'supplier_id', 'goods_id', 'amount', 'price', 'created_at', 'updated_at'];

    /**
     * @description 采购单导出-以商品为维度获取要导出的数据
     * @author zt7927
     * @data 2019/3/28 15:00
     * @param $ids
     * @return mixed
     */
    public function getPurchaseOrderGoods($ids)
    {
        $db = DB::table($this->table);
        //审核与完成状态的采购单
        $db = $db->leftjoin('procurement_plan','procurement_plan_goods.procurement_plan_id','=','procurement_plan.id')
                 ->leftjoin('purchase_order','procurement_plan.purchase_order_id','=','purchase_order.id')
                 ->leftjoin('goods','procurement_plan_goods.goods_id','=','goods.id')
                 ->leftjoin('setting_warehouse','purchase_order.warehouse_id','=','setting_warehouse.id')
                 ->leftjoin('setting_logistics','purchase_order.logistics_id','=','setting_logistics.id')
//                 ->whereIn('purchase_order.id', $ids)->whereBetween('purchase_order.status',[2,4])
                 ->whereIn('purchase_order.id', $ids)
                 ->select('goods.sku','purchase_order.order_no','procurement_plan.procurement_no',
                           'setting_warehouse.warehouse_name','setting_logistics.logistic_name',
                           'purchase_order.tracking_no','purchase_order.freight','purchase_order.status',
                           'procurement_plan_goods.amount','procurement_plan_goods.price')
                 ->get()->toArray();
        return $db;
    }

    /**
     * @description 获取采购商品
     * @author zt7927
     * @date 2019/4/4 17:27
     * @param $ids
     * @return mixed
     */
    public function getProcurementGoodsByIds($ids)
    {
        $ids = explode(',', $ids);                      //采购计划ids
        $db = DB::table($this->table);
        $db = $db->leftjoin('goods','procurement_plan_goods.goods_id','=','goods.id')
            ->leftjoin('procurement_plan','procurement_plan.id','=','procurement_plan_goods.procurement_plan_id')
            ->whereIn('procurement_plan.id',$ids)
            ->select('goods.sku',DB::raw('sum(procurement_plan_goods.amount) as amount'))
            ->groupBy('goods.sku')
            ->get()->toArray();
        return $db;
    }

    public function getId($id)
    {
        if (is_numeric($id) && $id > 0){
            return self::where('goods_id', $id)->get()->toArray();
        }
        return false;
    }
}

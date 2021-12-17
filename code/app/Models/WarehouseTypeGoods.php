<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class WarehouseTypeGoods
 * @description: 仓库商品与仓库中间表
 * @author: zt7927
 * @data: 2019/3/26 15:19
 * @package App\Models
 */
class WarehouseTypeGoods extends Model
{
    protected $table = 'warehouse_type_goods';

    public $timestamps = true;

    public $primaryKey = 'id';

    const SMTYPE = 1;

    const SELFDEFINE_TYPE = 2;

    public $fillable = ['id', 'goods_id', 'setting_warehouse_id', 'purchase_inventory', 'created_man', 'in_transit_inventory', 'drop_shipping', 'available_in_stock', 'created_at', 'updated_at','user_id'];


    /**
     * @description 库存查询
     * @author zt7927
     * @date 2019/4/10 16:50
     * @param array $params
     * @return mixed
     */
    public function search($params = [])
    {
        $db = DB::table($this->table);
        $db = $db->leftjoin('goods','goods.id','=','warehouse_type_goods.goods_id')
                 ->leftjoin('setting_warehouse', 'warehouse_type_goods.setting_warehouse_id', '=', 'setting_warehouse.id')
//                 ->leftjoin('inventory_allocation',function ($query){
//                     $query->on('goods.id','=','inventory_allocation.goods_id')
//                           ->on('setting_warehouse.id','=','inventory_allocation.warehouse_id');
//                 })
                 ->select('warehouse_type_goods.id','setting_warehouse.warehouse_name','goods.sku','goods.goods_pictures','goods.goods_name',
                          'warehouse_type_goods.purchase_inventory','warehouse_type_goods.in_transit_inventory',
                          'warehouse_type_goods.drop_shipping','warehouse_type_goods.available_in_stock',
                          'warehouse_type_goods.updated_at','goods.created_at','warehouse_type_goods.goods_id','warehouse_type_goods.setting_warehouse_id');

        $db->where('warehouse_type_goods.user_id', $params['user_id']);

        //所在仓库
        if (isset($params['warehouse_id']) && $params['warehouse_id']) {
            $db = $db->where('warehouse_type_goods.setting_warehouse_id', $params['warehouse_id']);
        }

        //自定义SKU
        if (isset($params['sku']) && $params['sku']) {
            $db = $db->where('goods.sku', 'like', '%'. $params['sku'] .'%');
        }
        //产品名称
        if (isset($params['goods_name']) && $params['goods_name']) {
            $db = $db->where('goods.goods_name', 'like', '%'. $params['goods_name'] .'%');
        }
        //产品分类
        if (isset($params['category_id']) && $params['category_id']) {
            $db = $db->where('goods.category_id_1', $params['category_id']);
        }

        return $db->orderBy('warehouse_type_goods.updated_at','DESC');
    }

    /**
     * @description 根据id获取数据
     * @author zt7927
     * @date 2019/4/11 14:28
     * @param $ids array 库存商品id
     * @return mixed
     */
    public function getWarehouseTypeGoodsByIds($ids)
    {
        $db = DB::table($this->table);
        $db = $db->whereIn('warehouse_type_goods.id', $ids)
            ->leftjoin('goods','goods.id','=','warehouse_type_goods.goods_id')
            ->leftjoin('setting_warehouse', 'warehouse_type_goods.setting_warehouse_id', '=', 'setting_warehouse.id')
            ->select('setting_warehouse.warehouse_name','goods.sku','goods.goods_name',
                'warehouse_type_goods.purchase_inventory','warehouse_type_goods.in_transit_inventory',
                'warehouse_type_goods.drop_shipping','warehouse_type_goods.available_in_stock',
                'warehouse_type_goods.updated_at')
            ->get();

        return $db;
    }

    /**
     * @description 根据商品id和仓库id查询
     * @author zt7927
     * @date 2019/4/11 17:32
     * @param $goods_id
     * @param $warehouse_id
     * @return Model|null|static
     */
    public function searchByGoodsIdAndWarehouseId($goods_id, $warehouse_id,$user_id)
    {
        return self::where('goods_id', $goods_id)->where('setting_warehouse_id', $warehouse_id)->where('user_id',$user_id)->first();
    }

    /**
     * @description 清空在途库存
     * @author zt7927
     * @date 2019/4/17 17:24
     * @param $data
     * @return bool
     */
    public static function emptyArr($data)
    {
        return self::where('goods_id', $data['goods_id'])
            ->where('setting_warehouse_id', $data['warehouse_id'])
            ->update([
                'in_transit_inventory' => 0
            ]);
    }

    /**
     * @description 更新商品库存
     * @author zt7927
     * @date 2019/4/15 16:29
     * @param $data
     * @param $status
     * @return bool
     */
    public static function updateArr($data, $status,$purchase_order_info)
    {
        $re = self::where('goods_id', $data['goods_id'])
            ->where('setting_warehouse_id', $data['warehouse_id'])->first();
        $updateData ['updated_at'] = date('Y-m-d H:i:s');
        if ($purchase_order_info ['status'] == PurchaseOrders::CHECK_STATUS) {
            //审核 转在途 采购库存 : -  在途库存 : +
            $updateData ['purchase_inventory'] = $re->purchase_inventory - $data['amount'];
        } else if ($purchase_order_info ['status'] == PurchaseOrders::ON_THE_WAY) {
            //在途 转完成状态 在途库存: - 可用库存: +
            $updateData ['in_transit_inventory'] = $re->in_transit_inventory - $data['amount'];
        }

        if ($status == PurchaseOrders::ON_THE_WAY && $purchase_order_info ['status'] == PurchaseOrders::CHECK_STATUS){
            $updateData ['in_transit_inventory'] = $data['amount'] + $re->in_transit_inventory;  //在途库存
            return self::where('id',$re->id)->update($updateData);
        } else if ($status == PurchaseOrders::COMPLETE && $purchase_order_info ['status'] == PurchaseOrders::ON_THE_WAY) {
            $updateData ['available_in_stock'] = $data['amount'] + $re->available_in_stock;     //可用库存
            return self::where('id',$re->id)->update($updateData);
        }
        return true;
    }

    /**
     * @param $goods_id
     * @param $plat_id
     * Note: 获取商品可销售库存 * 配货比
     * Data: 2019/7/1 15:32
     * Author: zt7785
     */
    public static function getAllocationQuantity($goods_id,$plat_id,$user_id)
    {
        if (empty($goods_id) || empty($plat_id) || empty($user_id)) {
            return 0;
        }
        $field = 'lotte as point';
        if ($plat_id == Platforms::AMAZON) {
            $field = 'amazon as point';
        }
        $inventory_allocation = InventoryAllocation::where('goods_id',$goods_id)->where('user_id',$user_id)->get([$field,'warehouse_id']);
        if ($inventory_allocation ->isEmpty()) {
            return 0;
        }
        $inventory_allocation = $inventory_allocation->toArray();

        $warehouseTypeGoods = new WarehouseTypeGoods();
        $wareGoodsInfo = $warehouseTypeGoods::where([
                'goods_id'=>$goods_id,
                'user_id'=>$user_id,
        ])->get(['id','available_in_stock','setting_warehouse_id']);
        if ($wareGoodsInfo ->isEmpty()) {
            return 0;
        }
        $wareGoodsInfo = $wareGoodsInfo->toArray();

        $goodsQuantity = 0;
        foreach ($wareGoodsInfo as $key => $value){
            $invoices_inv = OrdersInvoicesProducts::with('OrdersInvoices')->whereHas('OrdersInvoices',function ($query) use($value) {
                $query->where('invoices_status',OrdersInvoices::ENABLE_INVOICES_STATUS);
                $query->where('warehouse_id',$value ['setting_warehouse_id']);
                $query->where('delivery_status',OrdersInvoices::DELIVERY_STATUS_NO);
            })->where(['user_id'=>$user_id,'goods_id'=>$goods_id])->sum('already_stocked_number');
//                ->select('buy_number','already_stocked_number','invoice_id')->get();
            if ($invoices_inv) {
                $warehouseTypeGoods::where([
                    'id'=>$value['id'],
                ])->update(['drop_shipping' => $invoices_inv]);
            }

//            if ($invoicesInfo->isEmpty()) {
//                $invoices_inv = 0 ;
//            } else {
//                $already_stocked_number = $buy_number = 0 ;
//                $invoicesInfo = $invoicesInfo->toArray();
//                foreach ($invoicesInfo as $invoicesInfoKey => $invoicesInfoVal) {
//                    //已发货累加
//                    $already_stocked_number += $invoicesInfoVal['already_stocked_number'];
//                    $buy_number = $invoicesInfoVal ['buy_number'];
//                }
//                //购买数量减去发货数量
//                $invoices_inv =  $buy_number - $already_stocked_number;
//                $warehouseTypeGoods::where([
//                    'id'=>$value['id'],
//                ])->update(['drop_shipping' => $invoices_inv]);
//            }


            foreach ($inventory_allocation as $inventory_allocationKey => $inventory_allocation_val) {
                    if ($inventory_allocation_val ['warehouse_id'] == $value ['setting_warehouse_id']) {
                        $goodsQuantity += ($value ['available_in_stock']  - $invoices_inv) * $inventory_allocation_val ['point'];
                        break;
                    }
            }
        }
        //向下取整
        return intval(floor($goodsQuantity));
    }
}

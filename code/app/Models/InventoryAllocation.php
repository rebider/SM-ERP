<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryAllocation extends Model
{
    //
    protected $table = 'inventory_allocation';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'created_man', 'warehouse_id', 'goods_id', 'lotte', 'amazon', 'created_at', 'updated_at','user_id'];

    /**
     * @description 关联仓库表
     * @author zt7927
     * @date 2019/4/12 11:00
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(SettingWarehouse::class,'warehouse_id','id');
    }

    /**
     * @description 关联商品表
     * @author zt7927
     * @date 2019/4/12 11:01
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class,'goods_id', 'id');
    }

    /**
     * @description 库存分配查询
     * @author zt7927
     * @date 2019/4/12 11:40
     * @param array $params
     * @return mixed
     */
    public function search($params = [])
    {
        $db = DB::table($this->table);
        $db = $db->leftjoin('goods','inventory_allocation.goods_id','=','goods.id')
            ->leftjoin('setting_warehouse', 'inventory_allocation.warehouse_id', '=', 'setting_warehouse.id')
            ->leftjoin('warehouse_type_goods',function ($query){
                     $query->on('inventory_allocation.goods_id','=','warehouse_type_goods.goods_id')
                           ->on('inventory_allocation.warehouse_id','=','warehouse_type_goods.setting_warehouse_id');
                 })
            ->select('inventory_allocation.id','setting_warehouse.warehouse_name','goods.sku','goods.goods_name',
                     'warehouse_type_goods.drop_shipping', 'warehouse_type_goods.available_in_stock', 'inventory_allocation.updated_at',
                     'inventory_allocation.lotte','inventory_allocation.amazon','goods.id as goods_id');

        $db->where('inventory_allocation.user_id', $params['user_id']);
        //所在仓库
        if (isset($params['warehouse_id']) && $params['warehouse_id']) {
            $db = $db->where('inventory_allocation.warehouse_id', $params['warehouse_id']);
        }
        //自定义SKU
        if (isset($params['sku']) && $params['sku']) {
            $db = $db->where('goods.sku', $params['sku']);
        }
        //产品名称
        if (isset($params['goods_name']) && $params['goods_name']) {
            $db = $db->where('goods.goods_name', 'like', '%'. $params['goods_name'] .'%');
        }
        //产品分类
        if (isset($params['category_id']) && $params['category_id']) {
            $db = $db->where('goods.category_id_1', $params['category_id']);
        }

        return $db->orderBy('inventory_allocation.updated_at','DESC');
    }

    /**
     * @description 新增仓库分配
     * @author zt7927
     * @date 2019/4/11 18:03
     * @param $params
     * @return int
     */
    public function insertArr($params)
    {
        $data['created_man']  = $params['created_man'];
        $data['user_id']  = $params['user_id'];
        $data['created_at']   = date('Y-m-d H:i:s');
        $data['updated_at']   = date('Y-m-d H:i:s');
        $data['warehouse_id'] = $params['warehouse_id'];
        $data['goods_id']     = $params['goods_id'];
        $data['lotte']        = round($params['lotte'], 2);
        $data['amazon']       = round($params['amazon'], 2);

        return self::insertGetId($data);
    }

    /**
     * @description 编辑库存分配
     * @author zt7927
     * @date 2019/4/12 11:35
     * @param $params
     * @return bool
     */
    public function updatedArr($params)
    {
        $currentUser = CurrentUser::getCurrentUser();
        $data['created_man']  = $currentUser->userId;
        $data['lotte']        = round($params['lotte'], 2);
        $data['amazon']       = round($params['amazon'], 2);
        $data['updated_at']   = date('Y-m-d H:i:s');

        return self::where('id', $params['id'])->update($data);
    }

    /**
     * @description 查询分配比列
     * @author zt7927
     * @date 2019/4/11 18:25
     * @param $goods_id
     * @param $warehouse_id
     * @return Model|null|static
     */
    public function searchAllocation($goods_id, $warehouse_id,$user_id)
    {
        return self::where('goods_id', $goods_id)->where('warehouse_id', $warehouse_id)->where('user_id',$user_id)->first();
    }

    /**
     * @description 查询库存分配
     * @author zt7927
     * @date 2019/4/12 10:10
     * @param $id 库存分配-id
     * @return Model|null|static
     */
    public function getAllocationById($id)
    {
        return self::with('goods')->with('warehouse')->where('id', $id)->first();
    }

    /**
     * @description 验证数字
     * @author zt7927
     * @date 2019/4/12 14:51
     * @param $number
     * @return bool
     */
    public function checkNumber($number)
    {
        $rs = preg_match("/^\d+(?=\.{0,1}\d+$|$)/", $number);

        if ($rs) return true;

        return false;
    }
}

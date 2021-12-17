<?php

namespace App\Models;

use App\Auth\Common\CurrentUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Procurements
 * @description: 采购信息表
 * @author: zt7927
 * @data: 2019/3/19 15:51
 * @package App\Models
 */
class PurchaseGoodsBox extends Model
{
    protected $table = 'purchase_goods_box';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'sku','purchase_order_id', 'quantity', 'box_no', 'created_at', 'updated_at'];

    /**
     * @description 新增商品装箱数据
     * @author zt7927
     * @date 2019/4/9 14:45
     * @param $data
     * @param $purchase_id
     * @return bool
     */
    public function insertData($data,$purchase_id)
    {
        $currentUser = CurrentUser::getCurrentUser();
        $arr = [];
        for ($i = 0; $i < count($data); $i++){
            $arr['created_man'] = $currentUser->userId;
            $arr['sku'] = $data[$i]['sku'];
            $arr['quantity'] = $data[$i]['amount'];
            $arr['box_no'] = $data[$i]['box_no'];
            $arr['purchase_order_id'] = $purchase_id;
            $arr['created_at'] = date('Y-m-d H:i:s');
            $arr['updated_at'] = date('Y-m-d H:i:s');

            $re = self::insertGetId($arr);
            if (!$re){
                return false;
            }
        }
        return true;
    }

    /**
     * @description 根据采购单id查询商品装箱数据
     * @author zt7927
     * @date 2019/4/9 15:49
     * @param $id 采购单-id
     * @return array
     */
    public function getGoodsBox($id)
    {
        return self::where('purchase_order_id', $id)->get()->toArray();
    }
}

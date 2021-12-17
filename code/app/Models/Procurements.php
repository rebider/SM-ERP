<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Procurements
 * @description: 采购信息表
 * @author: zt7927
 * @data: 2019/3/19 15:51
 * @package App\Models
 */
class Procurements extends Model
{
    protected $table = 'procurement';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id', 'created_man', 'preferred_supplier_id', 'preferred_price', 'preferred_url', 'alternative_supplier_id', 'alternative_price', 'alternative_url', 'created_at', 'updated_at','user_id'];

    public function addGetId($insert_arr,$id,$user_id)
    {
        $goodsInfo = $this->where(['user_id'=>$user_id,'goods_id'=>$id])->first(['id']);
        if (empty($goodsInfo)) {
            $insert_arr['created_at'] = date('Y-m-d H:i:s');
            $insert_arr['updated_at'] = date('Y-m-d H:i:s');
            return $this->insertGetId($insert_arr) ;
        } else {
            $insert_arr['updated_at'] = date('Y-m-d H:i:s');
            return $this->where('id',$goodsInfo->id)->update($insert_arr);
        }
    }

}

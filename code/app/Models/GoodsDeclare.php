<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsDeclare extends Model
{
    protected $table = 'goods_declare';

    public $timestamps = true;

    public $primaryKey = 'id';

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

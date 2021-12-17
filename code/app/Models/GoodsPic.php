<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsPic extends Model
{
    protected $table = 'goods_pics';

    public $timestamps = true;

    public $primaryKey = 'id';

    public function insertArr($insert,$userInfo)
    {

        $insert['created_man'] = $userInfo ['created_man'] ;
        $insert['user_id'] = $userInfo ['user_id'] ;
        $insert['created_at'] = date('Y-m-d H:i:s') ; //todo 获取创建人
        $insert['updated_at'] = date('Y-m-d H:i:s') ; //todo 获取创建人
        $re = $this->insert($insert) ;
        if ($re){
            return $re ;
        }

        return false ;
    }
}

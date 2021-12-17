<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsLocalPic extends Model
{
        protected $table = 'goods_local_pics';

        public $timestamps = true;

        public $primaryKey = 'id';

        public function insertArr($insert)
        {
            $insert['created_at'] = date('Y-m-d H:i:s') ;
            $insert['updated_at'] = date('Y-m-d H:i:s') ;
            $re = $this->insert($insert) ;
            if ($re){
                return $re ;
            }

            return false ;
        }

        public function deleteImages($id,$user_id){
            return self::where(['goods_id' => $id,'user_id' => $user_id])->delete();
        }
}

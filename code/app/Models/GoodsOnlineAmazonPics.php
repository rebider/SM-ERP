<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsOnlineAmazonPics extends Model
{
    protected $table = 'goods_online_amazon_pics';
    public $timestamps = true;
    public $primaryKey = 'id';


    /**
     * @note
     * 根据goods_id删除图片
     * @since: 2019/6/10
     * @author: zt7837
     * @return: array
     */
    public static function delPicsById($id,$user_id) {
        return self::where(['goods_id'=>$id,'user_id'=>$user_id])->delete();
    }

}

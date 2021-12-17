<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsDraftAmazonPics extends Model
{
    protected $table = 'goods_draft_amazon_pics';
    public $timestamps = true;
    public $primaryKey = 'id';
    protected $fillable = ['goods_id','created_man','link','created_at','updated_at','user_id'];

    public function updateById($data) {
        return self::insert($data);
    }

    /**
     * @note
     * 根据goods_id删除图片
     * @since: 2019/6/10
     * @author: zt7837
     * @return: array
     */
    public function delPicsById($id,$user_id) {
        return self::where(['goods_id'=>$id,'user_id'=>$user_id])->delete();
    }
}

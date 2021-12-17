<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsAttribute extends Model
{
    protected $table = 'goods_attribute';

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','attribute_name','created_at','updated_at'];

    public static function getAllAttrs()
    {
        return self::get()->toArray();
    }
}

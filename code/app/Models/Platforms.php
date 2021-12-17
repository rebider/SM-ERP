<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class Platforms
 * Notes: 平台表
 * @package App\Models
 * Data: 2019/3/7 11:36
 * Author: zt7785
 */
class Platforms extends Model
{
    protected $table = 'platforms';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','name_EN','name_CN','created_at','updated_at'];

    const AMAZON = 1;

    const RAKUTEN = 2;
    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }

    public function SettingShops()
    {
        return $this->hasMany(SettingShops::class, 'plat_id', 'id')->select(['id', 'created_man', 'plat_id', 'shop_name', 'status', 'recycle']);
    }

    public static function getAllPlat()
    {
        return self::get(['id','name_CN','name_EN','created_man'])->toArray();
    }

    /**
     * @param $user_id
     * @return array
     * Note: 店铺权限
     * Data: 2019/3/26 18:52
     * Author: zt7785
     */
    public static function getAllPlatShops($user_id)
    {
        return self::with(['SettingShops'=>function ($query) use($user_id){
            //该客户下 未删除的店铺
            $query->where('user_id',$user_id);
            $query->where('recycle',SettingShops::SHOP_RECYCLE_UNDEL);
    }])->get()->toArray();
    }
}

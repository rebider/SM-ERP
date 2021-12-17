<?php

namespace App\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RolesShops
 * Notes: RBAC
 * @package App
 * Data: 2018/10/31 18:10
 * Author: zt7785
 */
class RolesShops extends Model
{
    protected $table = 'roles_shops';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','shop_user_id','shops_id','status','created_at','updated_at'];

    /**
     * @var 启用
     */
    const ROLE_ENABLE_STATE = 1;

    /**
     * @var 禁用
     */
    const ROLE_DISABLED_STATE = 0;

    /**
     * @param $user_id
     * @return Model|null|static
     * Note: 获取客户店铺权限
     * Data: 2019/3/28 10:29
     * Author: zt7785
     */
    public static function getShopPermissionByUserid($user_id)
    {
        return self::where('shop_user_id',$user_id)->where('status',self::ROLE_ENABLE_STATE)->first(['id','created_man','shop_user_id','shops_id','status']);
    }
}

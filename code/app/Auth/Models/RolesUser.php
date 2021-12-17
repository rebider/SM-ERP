<?php

namespace App\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RolesUser
 * Notes: RBAC
 * @package App
 * Data: 2018/10/31 18:10
 * Author: zt7785
 */
class RolesUser extends Model
{
    protected $table = 'roles_user';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','user_id','role_id','created_at','updated_at','state'];

    /**
     * @var 启用
     */
    const ROLE_USER_ENABLE_STATE = 1;

    /**
     * @var 禁用
     */
    const ROLE_USER_DISABLED_STATE = 0;

    //关联用户分组表
    public function roles()
    {
        return $this->belongsTo(Role::class,'role_id','id');
    }

    public static function getUserRoleInfo($user_id)
    {
        $collection = self::with('roles');
        return $collection->where('user_id',$user_id)->where('state',self::ROLE_USER_ENABLE_STATE)->first();
    }

    /**
     * @param $user
     * Note: 后台注册账号默认角色逻辑
     * Data: 2019/3/25 14:56
     * Author: zt7785
     */
    public static function defaultRoleLogic ($user)
    {
        $roleData ['user_id'] = $user->user_id;
        $roleData ['role_id'] = Role::PRIMARY_ROLE;
        $roleData ['state'] = self::ROLE_USER_ENABLE_STATE;
        self::create($roleData);
    }
}

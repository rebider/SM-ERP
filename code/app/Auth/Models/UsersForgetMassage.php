<?php

namespace App\Auth\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use \Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersForgetMassage extends User
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'user_forget_massage';

    /**
     * 与模型关联的数据表主键
     *
     * @var int
     */
    protected $primaryKey = 'id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    public $fillable = ["id","user_id","service_type","send_to","ip_addr","service_code","service_record","status","created_at","updated_at"];


    /**
     * @var 未使用
     */
    const CODE_STATUS_UNUSED = 0;

    /**
     * @var 已使用
     */
    const CODE_STATUS_USED = 1;

    /**
     * @var 已过期
     */
    const CODE_STATUS_PASSED = 2;


    /**
     * @var 短信
     */
    const TYPE_SMS = 1;

    /**
     * @var 邮箱
     */
    const TYPE_EMAIL = 2;

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type','is_deleted']);
    }

    /**
     * @param $user_id
     * @return array
     * Note: 获取客户生成的服务code
     * Data: 2019/3/29 9:59
     * Author: zt7785
     */
    public static function getCodeByUserid($user_id)
    {
     return   self::where('user_id',$user_id)->where('service_type',self::TYPE_EMAIL)->where('status',self::CODE_STATUS_UNUSED)->orderBy('created_at','desc')->get()->toArray();
    }

    /**
     * @param $ids
     * @return bool
     * Note: 批量更新过期code
     * Data: 2019/3/29 10:00
     * Author: zt7785
     */
    public static function overdueCode ($ids)
    {
        return self::whereIn('id',$ids)->update(['status'=>self::CODE_STATUS_PASSED,'updated_at'=>date('Y:m:d H:i:s')]);
    }

    /**
     * @param $code
     * Note: 根据code获取客户信息
     * Data: 2019/3/29 10:00
     * Author: zt7785
     */
    public static function getUserInfoBycode ($code)
    {
        return self::with('Users')->where('service_code',$code)->first();
    }

    /**
     * @param int $id
     * @param $data
     * @return Model
     * Note: updateOrCreate 返回模型
     * Data: 2019/3/12 19:07
     * Author: zt7785
     */
    public static function postDatas($id = 0, $data)
    {
        return self::updateOrCreate(['id' => $id], $data);
    }
}


<?php

namespace App\Auth\Models;

use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Controllers\RegisterController;
use App\Http\Controllers\Exchange\ExchangeController;
use \Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Users extends User
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * 与模型关联的数据表主键
     *
     * @var int
     */
    protected $primaryKey = 'user_id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden  = [
        'password'
    ];

    public $fillable = ['user_id','created_man','user_code','username','company_name','email','password','mobile','phone','address_country','address_province','address_city','address','remember_token','login_error_number','login_error_time','last_login_time','state','is_deleted','user_type','created_user_id','updated_user_id','created_at','updated_at'];


    /**
     * @var 启用
     */
    const USER_ENABLE_STATE = 1;

    /**
     * @var 禁用
     */
    const USER_DISABLED_STATE = 0;


    /**
     * @var 删除
     */
    const USER_ISDELETED = 1;

    /**
     * @var 未删除
     */
    const USER_UNDELETED = 0;

    /**
     * @var 基础设置
     */
    const BASE_SETTING_MENUS_ID = 6;
    /**
     * 根据用户id获取用户适用对象
     * @author zt6768
     * @param int $userId 用户id
     * @return object
     */
    public function getUserApply($userId)
    {
        return $this->find($userId)->userApply;
    }

    /**
     * 关联UserApply模型
     * @author zt6768
     * @return object
     */
    public function userApply()
    {
        return $this->hasOne('App\Auth\Models\UserApply', 'user_id');
    }

    /**
     * 根据用户id获取用户名
     * @author zt6768
     * @param int $id 用户id
     * @return string
     */
    public static function getUsernameById($id)
    {
        return self::find($id)->username;
    }


    /**
     * 重置密码方法
     * @param Request $request
     */
    public function set_password(Request $request){
        $id = Auth::user()->id;
        $oldpassword = $request->input('oldpassword');
        $newpassword = $request->input('newpassword');
        $res = DB::table('admins')->where('id',$id)->select('password')->first();
        if(!Hash::check($oldpassword, $res->password)){
            echo 2;
            exit;//原密码不对
        }
        $update = array(
            'password'  =>bcrypt($newpassword),
        );
        $result = DB::table('admins')->where('id',$id)->update($update);
        if($result){
            echo 1;exit;
        }else{
            echo 3;exit;
        }
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * Note: 后台客户注册编辑
     * Data: 2019/3/25 14:28
     * Author: zt7785
     */
    public static function userValidate($data,$is_creat = true)
    {
        $currentUser = CurrentUser::getCurrentUser();
        //2.数据处理
        //注册默认该字段为空
        $valideData ['user_code'] = isset($data ['user_code']) ? $data['user_code'] : '';
        $valideData ['username'] = isset($data ['username']) ? $data['username'] : '';
        $valideData ['company_name'] = isset($data ['company_name']) ? $data['company_name'] : '';
        $valideData ['email'] = isset($data ['email']) ? $data['email'] : '';
        $valideData ['mobile'] = isset($data ['mobile']) ? $data['mobile'] : '';
        $valideData ['address_country'] = isset($data ['address_country']) ?$data ['address_country'] : '' ;
        $valideData ['address_province'] = isset($data ['address_province']) ?$data ['address_province'] : '' ;
        $valideData ['address_city'] = isset($data ['address_city']) ?$data ['address_city'] : '' ;
        $valideData ['address'] = isset($data ['address']) ?$data ['address'] : '' ;
        //默认启用
        $valideData ['state'] = isset($data['state']) ? $data['state'] : self::USER_ENABLE_STATE ;
        $valideData ['is_deleted'] = isset($data['is_deleted']) ? $data['is_deleted'] : self::USER_UNDELETED ;
        //新增客户
        if ($is_creat) {
            $valideData ['user_type'] = isset($data['user_type']) ? $data['user_type'] : AccountType::PRIMARY ;
            $valideData ['created_man'] = isset($data ['created_man']) ? $data ['created_man'] : $currentUser->userId;
            $valideData ['created_user_id'] = isset($data ['created_user_id']) ? $data ['created_user_id'] : $currentUser->userId;
        } else {
            //更新
            $valideData ['updated_user_id'] = isset($data ['updated_user_id']) ? $data ['updated_user_id'] : $currentUser->userId;
        }
        $valideData ['password'] = Hash::make($data ['password']);
        //数据插入
        DB::beginTransaction();
        try {
            $userResult = self::create($valideData);
            //转存官方汇率到我的汇率
            if($userResult) {
                (new ExchangeController)->addSettingCurrencyExchangeMain($userResult->user_id);
            }
            //主账号自动分配权限角色
            if ($is_creat && $userResult->user_type == AccountType::PRIMARY ) {
                RolesUser::defaultRoleLogic($userResult);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * @param $option
     * @return \Illuminate\Database\Eloquent\Model|null|static
     * Note: 指定条件获取客户信息
     * Data: 2019/4/1 17:24
     * Author: zt7785
     */
    public static function getUserInfoByOpt($option)
    {
        $collection = self::select(['user_id','state','is_deleted','username','email']);
        foreach ($option as $k =>$v) {
            $collection->where($k,$v);
        }
        return $collection->first();
    }
}


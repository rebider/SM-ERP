<?php

namespace App\Auth\Services;


use App\Auth\Common\Config;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Enums\AccountType;
use App\Auth\Common\Response;
use App\Auth\Models\Role;
use App\Auth\Models\RolesUser;
use App\Auth\Models\UserApply;
use App\Auth\Models\Users;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Common\Complexify;
use App\Common\printComplexifyResult;

class UsersService
{

    /**
     * 用户状态
     * @author zt6768
     * @param int $stateKey 键值
     * @return array|string
     */
    public static function getState($stateKey = '')
    {
        $state = [
            0 => '禁用',
            1 => '启用',
        ];
        if (is_numeric($stateKey)) {
            return $state[$stateKey];
        }
        return $state;
    }

    /**
     * 根据角色id获取用户数据
     * @author zt7242
     * @param int $roleId 角色id
     * @return object
     */
    public static function getUserDataById($roleId)
    {

        return Users::where([
            ['role_id', $roleId],
            ['state',1],
        ])->get();

    }

    /**
     * 根据用户id获取用户数据
     * @author zt6768
     * @param int $userId 用户id
     * @return object
     */
    public static function getById($userId)
    {
        return Users::find($userId);
    }

    /**
     * 新增或编辑用户
     * @author zt6768
     * @param array $requestData 数据
     * @param int $userInfo 用户模型
     * @return boolean
     */
    public static function createOrUpdate($requestData, $currentUser,$userInfo = '')
    {
        DB::beginTransaction();
        $userModle = new Users();
        if ($userInfo) { //编辑
            $userModle = $userInfo;
            $userModle->is_deleted = 0;
        }
        $userModle->username = $requestData['username'];
        $userModle->email = $requestData['email'];
        $userModle->state = $requestData['state'];
        $userModle->phone = $requestData['phone']??'';
        $userModle->mobile = $requestData['mobile'];
        $userModle->address = $requestData['address']??'';
        $userModle->company_name = $requestData['company_name']??'';
        $userModle->updated_user_id = $currentUser->userId;
        if (empty($userInfo)) { //新增
            $userModle->user_code = $requestData['user_code'];
            $userModle->password = Hash::make($requestData['password']);
            $userModle->created_user_id = $currentUser->userId;
            $userModle->created_man = $currentUser->userId;
            $userModle->user_type = AccountType::CHILDREN;
        }
        $hasUser = $userModle->save();

        if ($hasUser) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 查询
     * @author zt6768
     * @param string $keyword 关键字
     * @param int $page  页面
     * @param int $limit 数量
     * @return array
     */
    public static function search($params, $page, $limit)
    {
        $result = [];
        $result['code'] = 0;
        $result['msg'] =  '暂无数据';
        $result['count'] = 0;
        $result['data'] = [];

        $current = CurrentUser::getCurrentUser();
        //1.判断客户类型
        if ($current->userAccountType == AccountType::CHILDREN) {
            //子账户将操作不了该页面
            return $result;
        }
        //2.获取客户id
        $user_id = $current->userId;
        $collection = Users::where('created_user_id',$user_id)->where('is_deleted',Users::USER_UNDELETED);

        $params['user_code'] && $collection->where('user_code','like','%'.$params['user_code'].'%');

        if (isset($params['state'])){
            $collection->where('state',$params['state']);
        }
        //总数
        $count = $collection->count();
        $page = $page-1;
        //数据
        $data = $collection->orderBy('user_id', 'desc')
            ->skip($limit * $page)
            ->take($limit)
            ->get()
            ->toArray();
        $result['msg'] =  empty($count) ? '暂无数据' : '';
        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 根据用户id获取用户名
     * @author zt6768
     * @param int|array $ids 用户id
     * @return array
     */
    public static function getUsernameById($ids)
    {
        if (!is_array($ids)) {
            $ids = (array)$ids;
        }
        $result = Users::whereIn('user_id', $ids)->get();
        $data = [];
        foreach ($result as $item) {
            $data[$item->user_id] = $item->username;
        }
        return $data;
    }

    /**
     * 修改密码
     * @author zt6768
     * @param array $requestData 数据
     * @return object
     */
    public static function updatePassword($requestData)
    {
        if (isset($requestData['user_id'])) {
            $userId = $requestData['user_id'];
        } else {
            $userId = CurrentUser::getCurrentUser()->userId;
        }
        $userModel = self::getById($userId);
        if (empty($userModel)) {
            return Response::isFailure('用户不存在');
        }
        $userModel->password = Hash::make($requestData['password']);
        $userModel->save();
        return Response::isSuccess();
    }

    /**
     * 检查密码规则
     * @param string $password 密码
     * @return boolean|string
     */
    public static function checkPasswordRule($password)
    {
        $checkPassword = new Complexify(['minComplexity' => 16]);
        $checkResult = $checkPassword->evaluateSecurity($password);
        $complexifyResult = new printComplexifyResult($checkResult);
        return $complexifyResult->tip();
    }

    /**
     * 根据用户id获取用户适用对象
     * @author zt6768
     * @param int $userId 用户id
     * @return object
     */
    public static function getUserApply($userId)
    {
        $model = new Users();
        return $model->getUserApply($userId);
    }


    /**
     * 登录操作
     * @param string $userCode 账号
     * @param string $password 密码
     * @param string $vcode    登录输入验证码
     * @param string $sessionVCode 验证码
     * @param boolean $requiredVCode
     * @return Response
     */
    public static function login($userCode, $password, $vcode, $sessionVCode, & $requiredVCode)
    {
        $user = Users::where(function($query) use($userCode){
            $query->where('user_code',$userCode);
            $query->where('is_deleted',Users::USER_UNDELETED);
        })->first();
        if (empty($user) === false) {
            //系统管理员 sumao_erp_admin
            $roleInfo = RolesUser::getUserRoleInfo ($user->user_id);
            if (config('app.admin') != $user->user_code) {
                if (empty($roleInfo)) {
                    //未分配角色 异常
                    return Response::isFailure(__("auth.roleAccountUnable"));
                }
                if ($roleInfo->roles->state == 0) { //角色禁用
                    return Response::isFailure(__("auth.roleAccountUnable"));
                }
                if ($user->state == 0) { //账号禁用
                    return Response::isFailure(__("auth.loginAccountUnable"));
                }
            }
            if ($userCode != $user->user_code) { //账号区分大小
                return Response::isFailure(__("auth.accountError"));
            }
//            if (Config::requiredVerifyCode() && $user->login_error_number >= Config::loginErrorNumberEnableVerifyCode()) {
//                $requiredVCode = true;
//                if (empty($vcode)) { //请输入验证码
//                    return Response::isFailure(__("auth.verifyCodeRequired"));
//                }
//                if (strtolower($vcode) != strtolower($sessionVCode)) { //验证错误
//                    return Response::isFailure(__("auth.verifyCodeError"));
//                }
//            }
        }
        if (empty($user) || Hash::check($password, $user->password) == false) {
            if (empty($user) == false) {
//                $user->login_error_number += 1;
//                $user->login_error_time = date('Y-m-d H:i:s');
//                if (Config::requiredVerifyCode() && $user->login_error_number >= Config::loginErrorNumberEnableVerifyCode()) {
//                    $requiredVCode = true;
//                }
//                $user->save();
            }
            return Response::isFailure(__("auth.accountError"));
        }
        $user->login_error_number = 0;
        $user->login_error_time = date('Y-m-d H:i:s');
        $user->last_login_time = date('Y-m-d H:i:s');
        $user->save();
        //返回客户模型 和客户角色菜单模型
        return Response::isSuccess('', ['userInfo'=>$user,'roleInfo'=>$roleInfo]);
    }

    /**
     * 根据账号获取用户信息
     * @author zt6768
     * @param string $userCode 账号
     * @return object
     */
    public static function getByUserCode($userCode)
    {
        return Users::where("user_code","=", $userCode)->first();
    }

    /**
     * @param $user_id
     * @return mixed
     * Note: 删除用户
     * Data: 2019/3/25 19:01
     * Author: zt7785
     */
    public static function delUserLogic($user_id) {
        //1.数据处理
        $user_id = array_filter(explode(',',$user_id));
        //2.客户关系
        $current = CurrentUser::getCurrentUser();
        //1.判断客户类型
        $result ['code'] = 201;
        $result ['msg'] = '';
        if ($current->userAccountType == AccountType::CHILDREN) {
            //子账户将操作不了该页面
            $result ['msg'] = '删除失败!权限异常';
            return $result;
        }
        $currentUserId = $current->userId;
        DB::beginTransaction();
        try {
            //删除状态
            $updateData ['is_deleted'] = Users::USER_ISDELETED;
            $updateData ['updated_user_id'] = $currentUserId;
            $updateRolesUserData ['updated_at'] = $updateRolesData ['updated_at'] = $updateData ['updated_at'] = date('Y:m:d H:i:s');
            foreach ($user_id as $value) {
                //该客户管理下的子账户才允许删除
               $updatRe =  Users::where('user_id',$value)->where('created_man',$currentUserId)->where('created_user_id',$currentUserId)->where('user_type',AccountType::CHILDREN)->update($updateData);
               if ($updatRe) {
                   //同时禁用角色信息
                   $rolesUserInfo = RolesUser::getUserRoleInfo($value);
                   //可能子账户未设置权限角色
                   if (empty($rolesUserInfo)) {
                       continue ;
                   } else {
                       //处理正常角色关联数据
                       if ($rolesUserInfo ['state'] != RolesUser::ROLE_USER_DISABLED_STATE ) {
                           RolesUser::where('user_id',$value)->where('state',RolesUser::ROLE_USER_ENABLE_STATE)->update($updateRolesUserData);
                       }
                       //可能客户分配角色未指定菜单 ?
                       if ($rolesUserInfo ['roles']) {
                           if ($rolesUserInfo ['roles'] ['state'] != Role::ROLE_DISABLED_STATE ) {
                               $updateRolesData ['state'] = Role::ROLE_DISABLED_STATE;
                               Role::where('id',$rolesUserInfo ['roles'] ['id'])->update($updateRolesData);
                           }
                       }
                   }
               } else {
                   //更新失败 回滚操作
                   DB::rollback();
                   $result ['msg'] = '删除失败!';
                   return $result;
               }
            }
            DB::commit();
            $result ['code'] = 200;
            $result ['msg'] = '删除成功!';
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            $result ['code'] = 202;
            $result ['msg'] = '删除失败!';
            return $result;
        }
    }

    public static function register($requestData)
    {
        DB::beginTransaction();
        $userModle = new Users();
        $userModle->username = $requestData['username'];
        $userModle->email = $requestData['email'];
        $userModle->state = Users::USER_ENABLE_STATE;
        $userModle->is_deleted = Users::USER_UNDELETED;
        $userModle->mobile = $requestData['mobile'];
        $userModle->company_name = $requestData['company_name'];
        $userModle->user_code = $requestData['user_code'];
        $userModle->password = Hash::make($requestData['password']);
        $userModle->user_type = AccountType::PRIMARY;
        $hasUser = $userModle->save();
        if ($hasUser) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }
}
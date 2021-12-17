<?php

namespace App\Auth\Controllers;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\StringExtension;
use App\Auth\Models\Menus;
use App\Auth\Models\Role;
use App\Auth\Models\RolesShops;
use App\Auth\Models\RolesUser;
use App\Auth\Models\Users;
use App\Auth\Services\RoleService;
use App\Auth\Services\UsersService;
use App\Auth\Validates\UsersValidate;
use App\Models\Platforms;
use App\Models\SettingShops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

/**
 * 用户管理控制器
 * Class UsersController
 * @package App\Auth\Controllers
 */
class UsersController extends BaseAuthController
{
    public $menusModel = '';
    public $roleModel = '';
    public $currentUser = '';

    public function __construct()
    {
//        $this->middleware(function ($request, $next) {
//            $this->currentUser  = CurrentUser::getCurrentUser();
//            return $next($request);
//        });
        $this->menusModel = new Menus();
        $this->roleModel  = new Role();
    }

    /**
     * 账号管理
     */
    public function index()
    {
        $responseData['shortcutMenus'] = $this->menusModel->getShortcutMenu(Users::BASE_SETTING_MENUS_ID);
        return view('Users.index')->with($responseData);
    }

    /**
     * 查询
     * @author zt6768
     * @return json
     */
    public function search(Request $request)
    {
        $data = $request ->all();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $param ['user_code'] = (isset($data['user_code']) && !empty($data['user_code'])) ? $data['user_code'] : '';
        $param ['state'] = $data['state'];
        $result = UsersService::search($param, $page, $limit);
        return response()->json($result);
    }

    /**
     * 新增账号
     * @author zt6768
     */
    public function add(Request $request)
    {
        $data = $request->all();
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser->userId)) {
            abort(404);
        }
        if ($request->isMethod('post')) {
            $validator = Validator::make(
                $data,
                UsersValidate::getUserCreateOrUpdate(true),
                UsersValidate::getMessages(),
                UsersValidate::getAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            } else {
//                if (strcmp($data ['password'],$data ['password_confirmation']) !== 0) {
//                    return AjaxResponse::isFailure('两次密码不一致');
//                }
                if ($request->password) { //检查密码复杂情况
                    $tip = UsersService::checkPasswordRule($request->password);
                    if ($tip) {
                        return AjaxResponse::isFailure($tip);
                    }
                }
                $result = UsersService::createOrUpdate($data,$currentUser);
                if ($result) {
                    return AjaxResponse::isSuccess('添加成功');
                } else {
                    return AjaxResponse::isFailure('添加失败');
                }
            }
        }
        return view('Users.add');
    }

    /**
     * 编辑账号
     * @author zt6768
     */
    public function edit(Request$request ,$id)
    {
        $userInfo = Users::find($id);
        if (empty($userInfo)) {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser->userId)) {
            abort(404);
        }
        $data = $request->all();
        $response ['user'] = $userInfo;
        if ($request->isMethod('post')) {
            //数据提交
                $validator = Validator::make(
                    $data,
                    UsersValidate::getUserCreateOrUpdate(false,$id),
                    UsersValidate::getMessages(),
                    UsersValidate::getAttributes()
                );
                if ($validator->fails()) {
                    return AjaxResponse::isFailure('', $validator->errors()->all());
                } else {
                    $result = UsersService::createOrUpdate($data,$currentUser,$userInfo);
                    if ($result) {
                        return AjaxResponse::isSuccess('编辑成功');
                    } else {
                        return AjaxResponse::isFailure('编辑失败');
                    }
                }
        } else if ($request->isMethod('get')){
            //数据渲染
            return view('Users.edit')->with($response);
        }
    }



    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 删除用户
     * Data: 2019/3/25 19:01
     * Author: zt7785
     */
    public function delUser (Request $request) {
        $data = $request->all();
        $user_ids = $data['ids'];
        $result = UsersService::delUserLogic($user_ids);
        return  response()->json($result);
    }

    /**
     * @param Request $request
     * Note: 菜单权限
     * Data: 2019/3/26 13:56
     * Author: zt7785
     */
    public function  menus (Request $request,$id) {
        $userInfo = Users::find($id);
        if (empty($userInfo)) {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser->userId)) {
            abort(404);
        }
        $data = $request->all();
        $userMenus = RolesUser::getUserRoleInfo($id);
        //客户可能未配置权限 那么数据为空
        $permissionArr = [];
        if (!empty($userMenus) || (isset($userMenus ['roles']) && !empty($userMenus ['roles'] ['menu_id']))) {
            $permissionArr = json_decode($userMenus['roles']['menu_id'], true);
        }
        $menusArr = $this->menusModel->getSecondByMap();
        array_multisort(array_column($menusArr,'parent_id'),SORT_ASC,$menusArr);
        if ($request->isMethod('post')) {
            //未全选将没有2级菜单
            $updateData = array_merge($data ['checkPar']??[] ,$data ['check']);
            //1级菜单必定有
            $updateData = array_unique(array_merge($data ['checkMenusPar']??[] ,$updateData));
            //默认给首页
            $updateData = array_merge($updateData,["1"]);
            sort($updateData);
            $updateData = json_encode($updateData);
            //菜单更新
            DB::beginTransaction();
            try {
                $status = false;
                if (!empty($userMenus) || (isset($userMenus ['roles']) && !empty($userMenus ['roles'] ['menu_id']))) {
                    $updateRe = $this->roleModel->where('id',$userMenus ['roles'] ['id'])->update(['menu_id'=>$updateData]);
                    if($updateRe) {
                        $status = true;
                    }
                } else {
                    //1.写角色表
                    $roles ['created_at'] = $roles ['updated_at'] = $rolesUser ['created_at'] = $rolesUser ['updated_at'] = date('Y:m:d H:i:s');
                    $roles ['created_man'] = $currentUser->userId;
                    $roles ['role_name'] = $currentUser->userName.'的子账户'.$userInfo->userName;
                    $roles ['menu_id'] = $updateData;
                    $roles ['state'] = Role::ROLE_ENABLE_STATE;
                    $insertRoleRe = $this->roleModel->insertGetId($roles);
                    //2.写权限表
                    $rolesUser ['created_man'] = $currentUser->userId;
                    $rolesUser ['user_id'] = $id;
                    $rolesUser ['role_id'] = $insertRoleRe;
                    $rolesUser ['state'] = RolesUser::ROLE_USER_ENABLE_STATE;
                    $insertRolesUserRe = RolesUser::insert($rolesUser);
                    if ($insertRoleRe && $insertRolesUserRe) {
                        $status = true;
                    }
                }
                if ($status) {
                    DB::commit();
                    return AjaxResponse::isSuccess('设置成功');
                } else {
                    DB::rollback();
                    return AjaxResponse::isFailure('设置失败');
                }
            } catch ( Exception $e) {
                DB::rollback();
                return AjaxResponse::isFailure('设置失败');
            }
        }
        return view('Users/menus')->with(['menus'=>$menusArr,'permission'=>$permissionArr]);
    }

    /**
     * @param Request $request
     * Note: 店铺权限
     * Data: 2019/3/26 13:56
     * Author: zt7785
     */
    public function shops (Request $request,$id) {
        $userInfo = Users::find($id);
        if (empty($userInfo)) {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        if (empty($currentUser->userId)) {
            abort(404);
        }
        $currentUser = CurrentUser::getCurrentUser();
        $shopsArr = Platforms::getAllPlatShops($currentUser->userId);
        $userRolesShops = RolesShops::getShopPermissionByUserid($id);
        $permissionArr = [];
        if ($userRolesShops) {
            $permissionArr = json_decode($userRolesShops['shops_id'],true);
        }
        $data = $request->all();
        if ($request->isMethod('post')) {
            $shops_check_id = $data ['check'];
            sort($shops_check_id);
            $updateShops = json_encode($shops_check_id);
            //菜单更新
            DB::beginTransaction();
            try {
                $status = false;
                $updateData ['shops_id'] = $updateShops;
                $updateData ['updated_at'] = date('Y:m:d H:i:s');
                if ($userRolesShops) {
                    $status = RolesShops::where('id',$userRolesShops['id'])->update($updateData);
                } else {
                    $updateData ['created_man'] = $currentUser->userId;
                    $updateData ['shop_user_id'] = $id;
                    $updateData ['status'] = RolesShops::ROLE_ENABLE_STATE;
                    $updateData ['created_at'] = $updateData ['updated_at'];
                    $status = RolesShops::insert($updateData);
                }

                if ($status) {
                    DB::commit();
                    return AjaxResponse::isSuccess('设置成功');
                } else {
                    DB::rollback();
                    return AjaxResponse::isFailure('设置失败');
                }
            } catch ( Exception $e) {
                DB::rollback();
                return AjaxResponse::isFailure('设置失败');
            }
        }
        return view('Users/shops')->with(['shops'=>$shopsArr,'permissionArrShop'=>$permissionArr]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return $this
     * Note: 个人信息
     * Data: 2019/3/28 10:09
     * Author: zt7785
     */
    public function detail (Request $request,$id) {
        $responseData['userInfo'] = Users::find($id);
        if (empty($responseData['userInfo'])) {
            abort(404);
        }
        $responseData['shortcutMenus'] = $this->menusModel->getShortcutMenu(Users::BASE_SETTING_MENUS_ID);
        return view('Users/detail')->with($responseData);
    }

    /**
     * 修改密码
     * @author zt6768
     */
    public function editPassword(Request $request)
    {
            $requestData = StringExtension::trim($request->all());
            $validator = Validator::make(
                $requestData,
                UsersValidate::getRulesUpdatePassword(),
                UsersValidate::getMessages(),
                UsersValidate::getAttributes()
            );
        if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            if ($request->password) { //检查密码复杂情况
                $tip = UsersService::checkPasswordRule($request->password);
                if ($tip) {
                    return AjaxResponse::isFailure($tip);
                }
            }
            $result = UsersService::updatePassword($requestData);
            if ($result->status == false) {
                AjaxResponse::isFailure($result->message);
            }
            //开启注释页面刷新将会提出 其他操作也会限制
//            Auth::logout();
//            CurrentUser::removeCurrentUser($request);
            return AjaxResponse::isSuccess('修改成功');
    }
}
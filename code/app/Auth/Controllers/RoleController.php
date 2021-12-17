<?php

namespace App\Auth\Controllers;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\StringExtension;
use App\Auth\Services\RoleService;
use App\Auth\Services\RoutePermissionsService;
use App\Auth\Validates\RoleValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * 角色控制器
 * Class RoleController
 * @package App\Auth\Controllers
 */
class RoleController extends BaseAuthController
{
    /**
     * 角色管理
     * @author zt6768
     */
    public function index()
    {
        return view("Role.index");
    }

    /**
     * 新增角色
     * @author zt6768
     */
    public function add()
    {
        return view('Role.add', [
            'roleAll' => RoleService::roleAll(),
            'stateAll' => RoleService::getState('')
        ]);
    }

    /**
     * 编辑角色
     * @author zt6768
     * @param int $id 角色id
     * @return mixed
     */
    public function edit($id)
    {
        $role = RoleService::getById($id);
        return view('Role.edit', [
            'role' => $role,
            'roleAll' => RoleService::roleAll(),
            'stateAll' => RoleService::getState('')
        ]);
    }

    /**
     * 提交保存角色
     * @author zt6768
     */
    public function store(Request $request)
    {
        $requestData = StringExtension::trim($request->all());
        $id = $request->input('id', 0);

        $validator = Validator::make(
            $requestData,
            $id ? RoleValidate::getRulesUpdate() : RoleValidate::getRulesCreate(),
            RoleValidate::getMessages(),
            RoleValidate::getAttributes()
        );
        if ($validator->fails()) {
            return AjaxResponse::isFailure('', $validator->errors()->all());
        } else {
            $result = RoleService::createOrUpdate($requestData, $id);
            if ($result) {
                return AjaxResponse::isSuccess('保存成功');
            } else {
                return AjaxResponse::isFailure('保存失败');
            }
        }
    }

    /**
     * 查询
     * @author zt6768
     * @return json
     */
    public function search(Request $request)
    {
        $roleName = $request->input('role_name','');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $result = RoleService::search($roleName, $page, $limit);
        return response()->json($result);
    }

    /**
     * 角色分配权限
     * @author zt6768
     */
    public function permissions($id)
    {
        $role = RoleService::getById($id);
        $permissions = RoutePermissionsService::getRoute();
        $rolePermissions = RoleService::getRolePermissionsByRoleId($id);
        $hasPermissions = [];
        if ($rolePermissions) {
            $hasPermissions = empty($rolePermissions->permissions) ? [] : explode(',', $rolePermissions->permissions);
        }
        return view('Role.permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'hasPermissions' => $hasPermissions
        ]);
    }

    /**
     * 提交保存分配权限
     * @author zt6768
     * @return \Illuminate\Http\JsonResponse
     */
    public function stroePermissions(Request $request)
    {
        $roleId = $request->input('role_id');
        $permissions = $request->input('permissions');

        $rolePermissions = RoleService::getRolePermissionsByRoleId($roleId);
        if (empty($rolePermissions)) {
            return AjaxResponse::isFailure('角色不存在');
        }
        $result = RoleService::createOrUpdateRolePermissions($permissions, $roleId, 'update');
        if ($result) {
            return AjaxResponse::isSuccess('保存成功');
        } else {
            return AjaxResponse::isFailure('保存失败');
        }
    }

    /**
     * 根据角色获取适用对象数据
     * @author zt6768
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        $roleId = $request->input('id');
        if (empty($roleId)) {
            return AjaxResponse::isFailure('请选择角色');
        }
        $data = RoleService::toObjectIds($roleId,1, 0);
        if (empty($data) && $roleId != 3) {
            return AjaxResponse::isFailure('未获取到适用对象');
        }
        return AjaxResponse::isSuccess('', $data);
    }

}
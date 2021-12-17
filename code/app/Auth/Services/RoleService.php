<?php
namespace App\Auth\Services;


use App\Auth\Common\CurrentUser;
use App\Auth\Models\Role;
use App\Auth\Models\RolePermissions;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * 角色类型
     * @author zt6768
     * @return array
     */
    public static function roleAll()
    {
        $role = [
            1 => '管理员',
            2 => '电商',
            3 => '售后',
            4 => '仓库',
            5 => '物流',
            6 => '其它'
        ];
        $role = array_merge(['0' => '请选择'], $role);
        return $role;
    }

    /**
     * 角色状态
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
     * 根据角色id获取角色数据
     * @author zt6768
     * @param int $id 角色id
     * @return object
     */
    public static function getById($id)
    {
        return Role::where('state',Role::ROLE_ENABLE_STATE)->find($id);
    }

    /**
     * 根据角色id获取角色名称
     * @author zt6768
     * @param int|array $ids 角色id
     * @return array
     */
    public static function getRoleNameById($ids)
    {
        if (!is_array($ids)) {
            $ids = (array)$ids;
        }
        $result = Role::whereIn('id', $ids)->where('state',Role::ROLE_ENABLE_STATE)->get();
        $data = [];
        foreach ($result as $item) {
            $data[$item->id] = $item->role_name;
        }
        return $data;
    }

    /**
     * 新增或编辑角色
     * @author zt6768
     * @param array $requestData 数据
     * @param int $id 角色id
     * @return boolean
     */
    public static function createOrUpdate($requestData, $id = 0)
    {
        DB::beginTransaction();
        $model = new Role();
        $currentUser = CurrentUser::getCurrentUser();
        if ($id) { //编辑
            $model = $model->find($id);
            $model->remark = htmlspecialchars($requestData['remark']);
            $model->state = $requestData['state'];
            $model->updated_user_id = $currentUser->userId;
            $bool = $model->save();
        } else { //新增
            $role = self::roleAll();
            $model->role_id = $requestData['role_id'];
            $model->role_name = $role[$requestData['role_id']];
            $model->remark = htmlspecialchars($requestData['remark']);
            $model->state = $requestData['state'];
            $model->created_user_id = $currentUser->userId;
            $model->updated_user_id = $currentUser->userId;
            $boolRole = $model->save();

            //默认角色权限
            $roleDefaultPermissions = RoutePermissionsService::getRoleDefaultPermissionsByRoleId($requestData['role_id']);
            $boolRolePermissions = self::createOrUpdateRolePermissions($roleDefaultPermissions, $requestData['role_id'], 'create');
            $bool = $boolRole && $boolRolePermissions;
        }
        if ($bool) {
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
     * @param string $roleName 角色名称
     * @param int $page  页面
     * @param int $limit 数量
     * @return array
     */
    public static function search($roleName, $page, $limit)
    {
        if ($roleName) {
            $model = Role::where(['role_name' => $roleName]);
        } else {
            $model = new Role();
        }
        $model->where('state',Role::ROLE_ENABLE_STATE);
        //总数
        $count = $model->count();
        $page = $page-1;
        //数据
        $data = $model->orderBy('role_id', 'desc')
            ->skip($limit * $page)
            ->take($limit)
            ->get()
            ->toArray();
        $createdUserIds = array_column($data,'created_user_id');
        $updatedUserIds = array_column($data,'updated_user_id');
        $userIds = array_merge($createdUserIds, $updatedUserIds);
        $usernames = UsersService::getUsernameById($userIds);
        foreach ($data as $key => $item) {
            $data[$key]['state_name'] = self::getState($item['state']);
            $data[$key]['state_name'] = self::getState($item['state']);
            $data[$key]['state_name'] = self::getState($item['state']);
            $data[$key]['created_user_id'] = isset($usernames[$item['created_user_id']]) ? $usernames[$item['created_user_id']] : '';
            $data[$key]['updated_user_id'] = isset($usernames[$item['updated_user_id']]) ? $usernames[$item['updated_user_id']] : '';
        }
        $result = [];
        $result['code'] = 0;
        $result['msg'] = $count == 0 ? '暂无数据' : '';
        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 根据条件获取角色
     * @author zt6768
     * @param array $map 条件
     * @param boolean $filter 过滤条件
     * @return object|null
     */
    public static function getByConditon($map, $filter = false)
    {
        $where = [];
        if (isset($map['state'])) {
            $where['state'] = $map['state'];
        }
        $model = Role::where($where);
        if ($filter) { //过滤管理员角色
            $model = $model->where('role_id', '!=', 1);
        }
        return $model->get();
    }

    /**
     * 根据角色id转换适用对象
     * @author zt6768
     * @param int $roleId 角色id
     * @param int $state  状态
     * @param int $applyObjectId 适用对象id
     * @return array
     */
    public static function toObjectIds($roleId, $state = 1, $applyObjectId = 0)
    {
        $map = [];
        if ($state) {
            $map['state'] = $state;
        }
        $data = [];
        if ($roleId == 2) { //电商
            $platform = Platform::getByCondition($map);
            foreach ($platform as $key => $item) {
                $data[$key]['roleId-id'] = $roleId.'-'.$item->platform_id;
                $data[$key]['name'] = $item->platform_name;
                $data[$key]['selected'] = $applyObjectId == $item->platform_id ? 1 : 0;
            }
        }
        if ($roleId == 3) { //售后
            $data = [];
        }
        if ($roleId == 4) { //仓库
            $warehouse = Warehouse::getByCondition($map);
            foreach ($warehouse as $key => $item) {
                $data[$key]['roleId-id'] = $roleId.'-'.$item->warehouse_id;
                $data[$key]['name'] = $item->warehouse_name;
                $data[$key]['selected'] = $applyObjectId == $item->warehouse_id ? 1 : 0;
            }
        }
        if ($roleId == 5) { //物流
            if ($applyObjectId) {
                $map['logistics_id'] = $applyObjectId;
            }
            $logistics = Logistics::getByCondition($map);
            foreach ($logistics as $key => $item) {
                $data[$key]['roleId-id'] = $roleId.'-'.$item->logistics_id;
                $data[$key]['name'] = $item->logistics_name;
                $data[$key]['selected'] = $applyObjectId == $item->logistics_id ? 1 : 0;
            }
        }
        if ($roleId == 6) { //其它
            $platform = Platform::getByCondition($map);
            $newPlatform = [];
            foreach ($platform as $key => $item) {
                $newPlatform[$key]['roleId-id'] = $roleId.'-'.$item->platform_id;
                $newPlatform[$key]['name'] = $item->platform_name;
                $newPlatform[$key]['selected'] = $applyObjectId == $item->platform_id ? 1 : 0;
            }

            $warehouse = Warehouse::getByCondition($map);
            $newWarehouse = [];
            foreach ($warehouse as $key => $item) {
                $newWarehouse[$key]['roleId-id'] = $roleId.'-'.$item->warehouse_id;
                $newWarehouse[$key]['name'] = $item->warehouse_name;
                $newWarehouse[$key]['selected'] = $applyObjectId == $item->warehouse_id ? 1 : 0;
            }

            $logistics = Logistics::getByCondition($map);
            $newLogistics = [];
            foreach ($logistics as $key => $item) {
                $newLogistics[$key]['roleId-id'] = $roleId.'-'.$item->logistics_id;
                $newLogistics[$key]['name'] = $item->logistics_name;
                $newLogistics[$key]['selected'] = $applyObjectId == $item->logistics_id ? 1 : 0;
            }
            $data = array_merge($newPlatform, $newWarehouse, $newLogistics);
        }
        return $data;
    }

    /**
     * 新增或编辑角色权限
     * @author zt6768
     * @param array|string $permissions 权限节点
     * @param int $roleId 角色id
     * @param string $operationType 操作类型
     * @return boolean
     */
    public static function createOrUpdateRolePermissions($permissions, $roleId, $operationType = 'create')
    {
        $model = new RolePermissions();
        if ($operationType == 'create') {
            $model->role_id = $roleId;
        } else {
            $model = $model->where('role_id', $roleId)->first();
        }
        if (is_array($permissions)) {
            $permissions = array_unique($permissions);
            $permissions = implode(',', $permissions);
        }
        $model->permissions = empty($permissions) ? null : $permissions;
        return $model->save();
    }

    /**
     * 根据角色id获取权限节点id
     * @author zt6768
     * @param int $roleId 角色id
     * @return object|null
     */
    public static function getRolePermissionsByRoleId($roleId)
    {
        return RolePermissions::where('role_id', $roleId)->first();
    }
}
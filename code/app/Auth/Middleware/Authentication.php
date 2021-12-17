<?php

namespace App\Auth\Middleware;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Models\Menus;
use App\Auth\Models\RolesUser;
use App\Exceptions\DataNotFoundException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * 授权中间件
 * Class Authentication
 * @package App\Auth\Middleware
 */
class Authentication
{
    /**
     * 处理授权
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $currentUser = CurrentUser::getCurrentUser();
        // 判断用户是否已登录
        if (empty($currentUser) && empty(Auth::check())) {
            if ($request->ajax()) {
                return AjaxResponse::isFailure('请先登录');
            }
            return redirect('login');
        }
        //auth门面 和原逻辑兼容
        if (Auth::user() && empty($currentUser)) {
            $userCode = Auth::user()->userCode;
            $roleInfo = RolesUser::getUserRoleInfo (Auth::user()->user_id);
            $userRoleInfo = $roleInfo['roles'];
            $menusModel = new Menus();
            $userNavigations = $menusModel->getMapMenuList(json_decode($userRoleInfo['menu_id'], true),Auth::user()->user_type);
            $userPermissions = array_column($userNavigations['permissions'], 'url');
            //兼容原逻辑
            $currentUser = new CurrentUser();
            $currentUser->userId = Auth::user()->user_id;
            $currentUser->userCode = Auth::user()->user_code;
            $currentUser->userName = Auth::user()->username;
            $currentUser->companyName = Auth::user()->company_name;
            //账号类型 1:系统管理，2:客户 3:客户子账户
            $currentUser->userAccountType = Auth::user()->user_type;
            //子账号上级id
            $currentUser->userParentId = Auth::user()->created_user_id;
            $currentUser->userRoleInfo = $userRoleInfo;
            $currentUser->userNavigation = $userNavigations['menusNav'];
            $currentUser->userPermissions = $userPermissions;
            CurrentUser::setCurrentUser($currentUser);
        } else if($currentUser) {
            $userCode = $currentUser->userCode;
            $userPermissions = $currentUser->userPermissions;
        }
        $isPermission = false;
        if (config('app.admin') != $userCode) {
            $url = $request->getPathInfo();
            if ($url == '/' || $url == '/getUserMenu') {
                $isPermission = false;
            } else {
                $isPermission = true;
                foreach ($userPermissions as $p) {
                    if (empty($p)) {
                        continue;
                    }
                    if (strpos($url, $p) !== false) {
                        $isPermission = false;
                        break;
                    }
                }
            }
        }
        if ($isPermission) {
            if ($request->ajax()) {
                return AjaxResponse::isFailure('账号没有权限执行此操作');
            }
            throw new DataNotFoundException('账号没有权限执行此操作', 888);
        }
        return $next($request);
    }
}
<?php

namespace App\Auth\Controllers;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Config;
use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use App\Auth\Services\RoleService;
use App\Auth\Services\RoutePermissionsService;
use App\Auth\Validates\UsersValidate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Auth\Services\UsersService;
use App\Auth\Common\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 登录控制器
 * Class LoginController
 * @package App\Auth\Controllers
 */
class LoginController extends Controller
{
    /**
     * 验证码session key
     * @var string
     */
    private $verifyCodeSessionKey = 'verifyCodeValue';


    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * 登录页面
     * @author zt6768
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        return view('Login/login');
        if (CurrentUser::getCurrentUser()) { //已登录
            return redirect('/');
        }
        return view("Login.index", [
            "requiredVerifyCode" => Config::requiredVerifyCode(),
            "loginErrorNumberEnableVerifyCode" => Config::loginErrorNumberEnableVerifyCode(),
        ])->with(["requiredVCode" => Config::requiredVerifyCode() && Config::loginErrorNumberEnableVerifyCode() == 0 ? true : false]);
    }

    /**
     * 登录操作
     * @author zt6768
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function doLogin(Request $request)
    {
        if (empty($request->input("userCode")) || empty($request->input("password"))) {
            return redirect("/login")->with([
                "message" => __("auth.userCodePasswordRequired")
            ]);
        }

        $requiredVCode = false;
        $sessionVCode = $request->session()->get($this->verifyCodeSessionKey, '');
        if ($sessionVCode) { //有验证码出现就验证
            if (strtolower($sessionVCode) != strtolower($request->verifyCode)) {
                return redirect("/login")->with([
                    "message" => __("auth.verifyCodeRequired"),
                    "userCode" => $request->userCode,
                    "requiredVCode" => true
                ]);
            }
        }

        $result = UsersService::login($request->userCode, $request->password, $request->verifyCode, $sessionVCode, $requiredVCode);
        if ($result->status) {
            $user = $result->data['userInfo'];
            $roleInfo = $result->data['roleInfo'];
            //用户角色信息
            $userRoleInfo = $roleInfo['roles'];
            //用户菜单
            $menusModel = new Menus();
            $userNavigations = $menusModel->getMapMenuList(json_decode($userRoleInfo['menu_id'], true),$user->user_type);

            $currentUser = new CurrentUser();
            $currentUser->userId = $user->user_id;
            $currentUser->userCode = $user->user_code;
            $currentUser->userName = $user->username;
            $currentUser->companyName = $user->company_name;
            //账号类型 1:系统管理，2:客户 3:客户子账户
            $currentUser->userAccountType = $user->user_type;
            //子账号上级id
            $currentUser->userParentId = $user->created_user_id;
            $currentUser->userRoleInfo = $userRoleInfo;
            $currentUser->userNavigation = $userNavigations['menusNav'];
            if (empty($userNavigations['permissions'])) {
                $currentUser->userPermissions = [];
            } else {
                $currentUser->userPermissions = array_column($userNavigations['permissions'], 'url');
            }
            CurrentUser::setCurrentUser($currentUser);
            //Auth注册
            Auth::login($user, true);
            if (empty($request->input("returnUrl"))) {
                return redirect("/");
            } else {
                if (strpos($request->input("returnUrl"), '/') === 0) {
                    return redirect($request->input("returnUrl"));
                } else {
                    return redirect("/");
                }
            }
        }
        return redirect("/login")->with([
            "message" => $result->message,
            "userCode" => $request->userCode,
            "requiredVCode" => $requiredVCode
        ]);
    }

    /**
     * 验证码
     * @param Request $request
     */
    public function verifyCode(Request $request)
    {
        header("Content-type: image/jpeg");
        $captcha = new CaptchaBuilder;
        $captcha->build()->output();
        $request->session()->flash($this->verifyCodeSessionKey, $captcha->getPhrase());
    }

    /**
     * 根据账号错误次数判断是否显示验证码
     * @author zt6768
     */
    public function requiredVerifyCode(Request $request)
    {
        if (empty($request->input("userCode")) == false) {
            $user = UsersService::getByUserCode($request->input("userCode"));
            if (empty($user) == false && $user->login_error_number >= Config::loginErrorNumberEnableVerifyCode()) {
                return AjaxResponse::isSuccess("", true);
            }
        }
        return AjaxResponse::isSuccess("", false);
    }

    /**
     * 退出登录
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        Auth::logout();
        CurrentUser::removeCurrentUser($request);
        return redirect("/login");
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Note: 忘记密码
     * Data: 2019/3/28 16:28
     * Author: zt7785
     */
    public function forget(Request $request)
    {
        if ($request->isMethod('post')) {

        }
        return view("Login/forget");
    }

}

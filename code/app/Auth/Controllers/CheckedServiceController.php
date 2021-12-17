<?php

namespace App\Auth\Controllers;

use App\Auth\Common\AjaxResponse;
use App\Auth\Common\CurrentUser;
use App\Auth\Common\Config;
use App\Auth\Models\Menus;
use App\Auth\Models\Users;
use App\Auth\Models\UsersForgetMassage;
use App\Auth\Services\RoleService;
use App\Auth\Services\RoutePermissionsService;
use App\Auth\Validates\UsersValidate;
use App\Common\Common;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mail\MailController;
use Illuminate\Http\Request;
use App\Auth\Services\UsersService;
use App\Auth\Common\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;


/**
 * Class CheckedServiceController
 * Notes: email|SMS code校验
 * @package App\Auth\Controllers
 * Data: 2019/3/29 10:14
 * Author: zt7785
 */
class CheckedServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (empty($request->input ('__token'))) {
                abort(404);
            }
            return $next($request);
        });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Note: code校验
     * Data: 2019/3/29 10:05
     * Author: zt7785
     */
    public function checked (Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $validator = Validator::make(
                $data,
                UsersValidate::getRulesUpdatePassword(),
                UsersValidate::getMessages(),
                UsersValidate::getAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            //1.根据token找客户信息
            $userInfo = UsersForgetMassage::getUserInfoBycode($data['__token']);
            if (empty($userInfo)) {
                return AjaxResponse::isFailure('访问数据异常');
            }
            //联表失败 空数据
            if (empty($userInfo['Users'])) {
                return AjaxResponse::isFailure('用户数据异常');
            }
            $userInfo = $userInfo->toArray();
            //过期
            if ($userInfo['status'] == UsersForgetMassage::CODE_STATUS_PASSED) {
                return AjaxResponse::isFailure('链接过期');
            }

            if ((time() - strtotime($userInfo['created_at'])) >= 30*60 ) {
                UsersForgetMassage::overdueCode([$userInfo ['id']]);
                return AjaxResponse::isFailure('链接过期');
            }

            //已使用
            if ($userInfo['status'] == UsersForgetMassage::CODE_STATUS_USED) {
                return AjaxResponse::isFailure('链接失效');
            }
            if ($userInfo['users'] ['state'] == Users::USER_DISABLED_STATE) {
                return AjaxResponse::isFailure('账号已禁用');
            }

            if ($userInfo['users'] ['is_deleted'] == Users::USER_ISDELETED) {
                return AjaxResponse::isFailure('账号已被删除');
            }
            //2.重置密码逻辑
            if ($request->password) { //检查密码复杂情况
                $tip = UsersService::checkPasswordRule($request->password);
                if ($tip) {
                    return AjaxResponse::isFailure($tip);
                }
            }
            $data['user_id'] = $userInfo['user_id'];
            $result = UsersService::updatePassword($data);
            if ($result->status == false) {
                AjaxResponse::isFailure($result->message);
            }
            $emailData ['status'] = UsersForgetMassage::CODE_STATUS_USED;
            UsersForgetMassage::postDatas($userInfo ['id'],$emailData);
            return AjaxResponse::isSuccess('重置成功');
        }
        return view ('Login/reset');
    }

}

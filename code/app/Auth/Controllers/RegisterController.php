<?php

namespace App\Auth\Controllers;

use App\Auth\Common\AjaxResponse;
use App\Auth\Models\Users;
use App\Auth\Models\UsersForgetMassage;
use App\Auth\Validates\UsersValidate;
use App\Common\Common;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mail\MailController;
use Illuminate\Http\Request;
use App\Auth\Services\UsersService;
use Illuminate\Support\Facades\Validator;


/**
 * 注册找回密码账户相关
 * Class LoginController
 * @package App\Auth\Controllers
 */
class RegisterController
{
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
            $data = $request->all();
            $rules ['email'] ='required|email';
            $validator = Validator::make(
                $data,
                $rules,
                UsersValidate::getMessages(),
                UsersValidate::getUserregisterRuleAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            }
            //V1 邮箱校验
            $option ['email'] = $data['email'];
            $userInfo = Users::getUserInfoByOpt($option);
            if (empty($userInfo)) {
                return AjaxResponse::isFailure('账号不存在');
            }
            $userInfo = $userInfo->toArray();
            if ($userInfo ['state'] == Users::USER_DISABLED_STATE) {
                return AjaxResponse::isFailure('账号已禁用');
            }

            if ($userInfo ['is_deleted'] == Users::USER_ISDELETED) {
                return AjaxResponse::isFailure('账号已被删除');
            }
            //V2 生成token
            //检测客户进半个小时是否有发送过
            $userForgetInfo = UsersForgetMassage::getCodeByUserid($userInfo['user_id']);
            $emailData ['user_id'] = $userInfo['user_id'];
            $emailData ['service_type'] = UsersForgetMassage::TYPE_EMAIL;
            $emailData ['status'] = UsersForgetMassage::CODE_STATUS_UNUSED;
            $emailData ['send_to'] = $option ['email'];
            $emailData ['ip_addr'] = Common::getIp();
            $insertStatus = false;
            if (empty($userForgetInfo)) {
                //生成
                $emailData ['service_code'] = $token = md5(Common::getUUid());
                $insertStatus = true;
            } else {
                //30分钟逻辑
                if ((time() - strtotime($userForgetInfo[0]['created_at'])) < 30*60 ) {
                    $token = $userForgetInfo[0]['service_code'];
                    $insertStatus = false;
                } else {
                    //生成并过期
                    UsersForgetMassage::overdueCode(array_column($userForgetInfo,'id'));
                    $emailData ['service_code'] = $token = md5(Common::getUUid());
                    $insertStatus = true;
                }
            }
            //V3 组装链接
            $validate_url = url('checked').'?__token='.$token;
            $mailService = new MailController ();
            $mailParam ['email'] = $option ['email'];
            $mailParam ['username'] = $userInfo ['username'];
            $mailParam ['url'] = $validate_url;
            $mailStatus = $mailService->forget($mailParam,$userInfo);
            if ($mailStatus['status'] == false) {
                return AjaxResponse::isFailure($mailStatus['msg']);
            }

            if ($insertStatus) {
                $emailData ['service_record'] = $mailStatus['record'];
                UsersForgetMassage::postDatas(0,$emailData);
            }
            return AjaxResponse::isSuccess($mailStatus['msg']);
        }
        return view("Login/forget");
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Note: 注册
     * Data: 2019/3/28 16:29
     * Author: zt7785
     */
    public function register(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = array_filter($request->all());
            $validator = Validator::make(
                $data,
                UsersValidate::getUserregisterRule(),
                UsersValidate::getMessages(),
                UsersValidate::getUserregisterRuleAttributes()
            );
            if ($validator->fails()) {
                return AjaxResponse::isFailure('', $validator->errors()->all());
            } else {
                if ($request->password) { //检查密码复杂情况
                    $tip = UsersService::checkPasswordRule($request->password);
                    if ($tip) {
                        return AjaxResponse::isFailure($tip);
                    }
                }
                $data ['created_man'] = 0;
                $data ['created_user_id'] = 0;
                $result = Users::userValidate($data);
                if ($result) {
                    return AjaxResponse::isSuccess('注册成功');
                } else {
                    return AjaxResponse::isFailure('注册失败');
                }
            }
        }
        return view("Login/register");
    }

}

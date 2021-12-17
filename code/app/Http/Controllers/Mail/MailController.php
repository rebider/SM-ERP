<?php

namespace App\Http\Controllers\Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController
{
    public $mailServiceStatus = true;

    public function __construct()
    {        //配置文件
        if (empty(env('MAIL_HOST')) || empty(env('MAIL_USERNAME')) || empty(env('MAIL_PASSWORD')) || empty(env('MAIL_FROM_ADDRESS')))
        {
            $this->mailServiceStatus = false;
        }
    }

    /**
     * @param $param
     * @param $userInfo
     * @return mixed
     * Note: 找回密码
     * Data: 2019/3/29 9:15
     * Author: zt7785
     */
    public function forget($param,$userInfo)
    {
        $response ['status'] = true;
        $response ['msg'] = '邮件发送成功';
        if (empty($this->mailServiceStatus)) {
            $response ['status'] = false;
            $response ['msg'] = '邮件服务异常';
            return $response;
        }
        $response ['record'] = Mail::send('Emails/forget',['email'=>$param ['email'],'username'=>$param['username'],'url'=>$param ['url']],function($message) use ($userInfo){
            $to = $userInfo['email'] ;
            $message ->to($to)->subject('速贸天下云仓平台邮箱找回密码');
        });
        if (Mail::failures()) {
            $response ['status'] = false;
            $response ['msg'] = '邮件发送失败';
            return $response;
        }
        return $response;
    }


    public function send($pwd,$cusInfo)
    {
        Mail::send('emails.mail',['email_address'=>$cusInfo['email_address'],'company_name'=>$cusInfo['company_name'],'pwd'=>$pwd],function($message) use ($cusInfo){
            $to = $cusInfo['email_address'] ;
            $message ->to($to)->subject('供应链金融密码重置');
        });

        if (Mail::failures()) {
            return ['code' => 'fail', 'info' => '密码重置失败！'];
        } else {
            return ['code' => 'success', 'info' => '密码已重置,请到邮箱查收！'];
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Note: 邮箱验证
     * Data: 2018/10/18 15:34
     * Author: zt7785
     */
    public function mailValidate (Request $request) {
        //TODO
        //163 SSL 端口 465
        //默认端口 25
        $data = $request->all();
        if (!isset($data['customer_id']) || empty($data['customer_id'])) {
            return response()->json(['code' => '201', 'mes' => '参数异常！']) ;
        }
        //配置文件
        if (empty(env('MAIL_HOST')) || empty(env('MAIL_USERNAME')) || empty(env('MAIL_PASSWORD')) || empty(env('MAIL_FROM_ADDRESS')) ) {
            return response()->json(['code' => '201', 'mes' => '邮件服务异常！请联系管理员']) ;
        }

        $cusInfo = CustomerInfo::where('id',$data['customer_id'])->where('customer_state',CustomerInfo::CUSTOMER_NORMAL)->first();
        if (empty($cusInfo)) {
            return response()->json(['code' => '201', 'mes' => '客户信息异常！']) ;
        }
        $uuid = SmsService::guid();
        $code = md5($uuid);
        $encryptStr = 'accout='.$cusInfo['accout'].'&contact_phone='.$cusInfo['contact_phone'].'&email_address='.$cusInfo['email_address'].'&valid_code='.$code;
//        $accessToken = Rsa::str_encode($encryptStr,'GYLJR');
        $accessToken = urlencode(Rsa::str_encode($encryptStr,'GYLJR'));
//        $validate_url = url('mail/checked',[
//            'accessToken' => $accessToken,
//        ]);
        $validate_url = url('mail/checked').'?accessToken='.$accessToken;
        $flag = Mail::send('emails.validate',['email_address'=>$cusInfo['email_address'],'company_name'=>$cusInfo['company_name'],'validate_url'=>$validate_url],function($message) use ($cusInfo){
            $to = $cusInfo['email_address'] ;
            $message ->to($to)->subject('供应链金融邮箱验证');
        });
        if (Mail::failures()) {
            return response()->json(['code' => '201', 'mes' => '邮件发送失败！']) ;
        } else {
            //写邮箱表
            $sendParam ['to'] = $cusInfo['email_address'];
            $sendParam ['code'] = $uuid;
            $sendParam ['service_record'] = $flag;
            $sendParam ['customer_id'] = $data['customer_id'];
            $sendParam ['ip_addr'] = ip2long(CusValidate::getIp());
            CusValidate::sendCode(CusValidate::MAIL_SERIVICE,$sendParam,parent::$redisService);
            return response()->json(['code' => '200', 'mes' => '邮件发送成功,请查收！']) ;
        }
    }

    /**
     * @param Request $request
     * @return $this
     * Note: 邮箱验证
     * Data: 2018/10/18 16:33
     * Author: zt7785
     */
    public function checked (Request $request) {
        $data = $request->all();
        if (!isset($data['accessToken']) || empty($data['accessToken'])) {
//            return abort(404);
            return view('emails/checked')->with(['code'=>201,'mes'=>'参数异常']);
        }
        $validate_param = Rsa::str_decode($data['accessToken'],'GYLJR');
        if (empty(strstr($validate_param, 'accout')) || empty(strstr($validate_param, 'contact_phone')) || empty(strstr($validate_param, 'email_address')) || empty(strstr($validate_param, 'valid_code'))) {
            return view('emails/checked')->with(['code'=>201,'mes'=>'参数异常']);
        }
        $validate_paramArr = explode('&',$validate_param);
        $paramArr = [];
        $valid_code = '';
        foreach ($validate_paramArr as $v) {
            $param = explode('=',$v);
            if ($param[0] != 'valid_code') {
                $paramArr[] = [$param [0],$param [1]];
            } else {
                $valid_code = $param[1];
            }
        }
        $cusInfo = CustomerInfo::where($paramArr)->first();
        if ($cusInfo) {
            if ($cusInfo['email_checked'] == CustomerInfo::EMAIL_CHECKED) {
                return view('emails/checked')->with(['code'=>200,'mes'=>'验证成功']);
            }
            //code校验
            $cusEmailCode = CusValidate::checkCode(CusValidate::MAIL_SERIVICE,['code'=>$valid_code,'customer_id'=>$cusInfo['id']],parent::$redisService);
            if ($cusEmailCode['code'] == 200) {
                CustomerInfo::postData($cusInfo['id'],['email_checked'=>CustomerInfo::EMAIL_CHECKED]);
                return view('emails/checked')->with(['code'=>200,'mes'=>'验证成功']);
            } else {
                return view('emails/checked')->with(['code'=>201,'mes'=>$cusEmailCode['mes']]);
            }
        } else {
            return view('emails/checked')->with(['code'=>201,'mes'=>'验证失败']);
        }
    }

}

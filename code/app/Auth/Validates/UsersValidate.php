<?php
namespace App\Auth\Validates;

class UsersValidate
{
    //创建规则
    public static function getRulesCreate()
    {
        return [
            'user_code' => 'required|max:10|unique:user|regex:/^([a-zA-Z0-9]+)$/',
            'username' => 'required|max:10|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u',
            'password' => 'required|max:8|max:50||confirmed|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i',
            'password_confirmation' => 'required|max:8|max:50||regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i',
            'email' => 'required|max:50|regex:/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',
            'role_id' => 'required|integer|exists:role,role_id',
            'state' => 'required|integer',
            'object_ids' => 'required_unless:role_id,3'
        ];
    }

    //更新规则
    public static function getRulesUpdate()
    {
        return [
            'user_id' => 'required|integer|exists:user,user_id',
            'username' => 'required|max:10|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9\.]+$/u',
            'email' => 'required|max:50|regex:/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',
            'role_id' => 'required|integer|exists:role,role_id',
            'state' => 'required|integer',
            'object_ids' => 'required_unless:role_id,3'
        ];
    }

    //修改密码规则
    public static function getRulesUpdatePassword()
    {
        return [
            'password' => 'required|min:8|max:50|confirmed|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i',
            'password_confirmation' => 'required|min:8|max:50|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i',
        ];
    }

    public static function getMessages()
    {
        return [
            'required' => '请输入:attribute',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度不超过:max字符',
            'user_code.unique' => '账号已存在，请重新输入',
            'user_code.regex' => '账号格式不正确，请输入字母、下划线和数字组成',
            'username.regex' => '请输入正确联系人姓名，不能输入特殊字符，如：!@#$%^&* 等（除“.”外）',
            'email.regex' => '邮箱格式不正确',
            'email.unique' => '已被注册的邮箱',
            'exists' => ':attribute不存在',
            'confirmed' => '密码不一致，请重新输入',
            'password.regex' => '密码格式不正确',
            'password_confirmation.regex' => '确认密码格式不正确',
            'mobile.regex' => '手机号格式不正确',
            'mobile.unique' => '已被注册的手机号',
        ];
    }

    public static function getAttributes()
    {
        return [
            'user_id' => '用户id',
            'user_code' => '账号名',
            'username' => '联系人',
            'password' => '密码',
            'password_confirmation' => '确认密码',
            'email' => '邮箱',
            'state' => '启用状态',
            'mobile' => '注册手机号',
            'phone' => '电话',
            'company_name' => '公司名称',
            'address' => '地址',
        ];
    }


    public static function getUserCreateOrUpdate($is_create = false,$user_id = '')
    {
        $rules = [
            'username' => 'min:2|max:50|regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9\.]+$/u',
            'phone'=>'nullable|min:5|max:50|regex:/^[0-9-]+$/u',//联系方式
            'company_name'=>'nullable|min:1|max:50|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',//公司名称
            'address'=>'nullable|min:3|max:100',//地址//regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u
            'state' => 'required|integer',
        ];
        if ($is_create) {
            $rules['user_code']  =  'required|min:5|max:50|unique:user|regex:/^(\w+)$/';
            $rules ['password'] = 'required|min:8|max:50|confirmed|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i';
            $rules ['password_confirmation'] = 'required|min:8|max:50|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i';
            $rules ['email'] = 'required|max:50|regex:/^([a-zA-Z0-9_\-][\.]?)+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/|unique:user,email';
            $rules ['mobile'] = 'required|max:18|regex:/^([\d-+]{7,20})+$/u|unique:user,mobile';
        } else {
            $rules ['user_id'] = 'required|integer|exists:user,user_id';
            $rules ['email'] = 'required|max:50|regex:/^([a-zA-Z0-9_\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/|unique:user,email,:id,user_id';
            $rules ['mobile'] = 'required|max:18|regex:/^([\d-+]{7,20})+$/u|unique:user,mobile,:id,user_id';//电话
            $rules = json_decode(str_replace(":id",$user_id,json_encode($rules)),true);
        }
        return $rules;
    }

    /**
     * @return mixed
     * Note: 注册
     * Data: 2019/4/1 17:22
     * Author: zt7785
     */
    public static function getUserregisterRule()
    {
        //账号
        $rules['user_code']  = 'required|min:5|max:50|regex:/^(\w+)$/|unique:user,user_code';
        //邮箱
        $rules ['email'] = 'required|max:50|regex:/^([a-zA-Z0-9_\-][\.]?)+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/|unique:user,email';
        //电话
        $rules['mobile']  = 'required|max:18|regex:/^([\d-+]{7,20})+$/u|unique:user,mobile';
        $rules ['password'] = 'required|min:8|max:50|confirmed|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i';
        $rules ['password_confirmation'] = 'required|min:8|max:50|regex:/^[A-Za-z0-9-_@=!#$%&*;.]+$/i';
        //联系人
        $rules['username']  =  'min:2|max:50|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\.]+$/u';

        //公司名
        $rules ['company_name'] = 'nullable|min:1|max:50|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u';
        $rules ['address_province'] = 'nullable|max:50|regex:/^[0-9a-zA-Z]+$/u';
        $rules ['address_city'] = 'nullable|max:50|regex:/^[0-9a-zA-Z]+$/u';
        $rules ['address'] = 'nullable|min:3|max:100';//regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u
        $rules ['phone'] = 'nullable|min:5|max:50|regex:/^[0-9-]+$/u';
        return $rules;
    }

    public static function getUserregisterRuleAttributes()
    {
        return [
            'user_code' => '账号名',
            'username' => '联系人',
            'password' => '密码',
            'password_confirmation' => '确认密码',
            'email' => '邮箱',
            'company_name' => '公司名称',
            'mobile' => '注册手机号',
            'phone' => '电话',
            'address' => '地址',
            'address_city' => '城市',
            'address_province' => '省份',
        ];
    }
}
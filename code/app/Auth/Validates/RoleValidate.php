<?php

namespace App\Auth\Validates;


class RoleValidate
{
    //创建规则
    public static function getRulesCreate()
    {
        return [
            'role_id' => 'required|integer|unique:role|regex:/^\+?[1-9]\d*$/',
            'remark' => 'max:100',
            'state' => 'required|integer'
        ];
    }

    //更新规则
    public static function getRulesUpdate()
    {
        return [
            'role_id' => 'required|integer|exists:role,role_id',
            'remark' => 'max:100',
            'state' => 'required|integer'
        ];
    }

    public static function getMessages()
    {
        return [
            'required' => ':attribute不能为空',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度最大:max位',
            'role_id.unique' => '角色类型已存在，请重新选择',
            'exists' => ':attribute不存在',
            'role_id.regex' => '请选择角色类型'
        ];
    }

    public static function getAttributes()
    {
        return [
            'role_id' => '角色类型',
            'remark' => '备注',
            'state' => '状态'
        ];
    }
}
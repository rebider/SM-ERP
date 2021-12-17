<?php

namespace App\Validates;


class OrderAddressValidate
{

    public static function getAddrRules()
    {
        return [
            'addressee_name' => 'required|min:1|max:30',
            'addressee' => 'required|min:1|max:80',
            'phone' =>'nullable|min:1|max:30',
            'province' => 'required|min:1|max:30',
            'city' => 'required|min:1|max:30',
            'postal_code' => 'required|min:1|max:30',
            'country_id' => 'required|integer',
            'warehouse_id' => 'nullable|integer',
            'logistics_id' => 'nullable|integer',
            'addressee_email' => 'nullable|email|min:3|max:50',
            'addressee1' => 'nullable|min:1|max:80',
            'mobile_phone' => 'nullable|min:1|max:30',
        ];
    }

    public static function getAddrMessages()
    {
        return [
            'required' => ':attribute不能为空',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度最大:max位',
            'min' => ':attribute长度最小:min位',
            'email'=>':attribute格式不正确',
            'regex'=>':attribute格式不正确',
        ];
    }

    public static function getAddrAttributes()
    {
        return [
            'addressee_name' => '收件人',
            'addressee_email' => '买家email',
            'addressee' => '地址1',
            'addressee1' => '地址2',
            'mobile_phone' => '手机',
            'phone' => '电话',
            'country_id' => '国家',
            'province' => '州/省',
            'city' => '城市',
            'postal_code' => '邮编',
            'warehouse_id' => '仓库',
            'logistics_id' => '物流方式',
        ];
    }
}
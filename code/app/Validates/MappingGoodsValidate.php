<?php

namespace App\Validates;


class MappingGoodsValidate
{

    public static function getRules($is_creat = false ,$trouble_id = 0)
    {
        $rules ['id'] =  'required|integer';
        $rules ['data'] =  'required';

        return $rules;
    }

    public static function getMessages()
    {
        return [
            'required' => ':attribute不能为空',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度最大:max位',
            'min' => ':attribute长度最小:min位',
            'unique' => ':attribute重复',
            'regex' => ':attribute格式不正确'
        ];
    }

    public static function getAttributes()
    {
        return [
            'id' => '商品id',
            'data' => '商品参数',
        ];
    }
}
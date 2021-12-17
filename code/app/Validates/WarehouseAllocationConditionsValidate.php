<?php

namespace App\Validates;


class WarehouseAllocationConditionsValidate
{
    /**
     * @return array
     * Note: 订单问题规则
     * Data: 2019/3/29 16:09
     * Author: zt7785
     */
    public static function getAllocationConditionsRules($is_creat = false ,$trouble_id = 0)
    {
        $rules ['opening_status'] =  'required|integer';
        if ($is_creat) {
            $rules ['trouble_rules_name'] = 'required|min:1|max:300|unique:rules_warehouse_allocation|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u';
        } else {
            $rules ['trouble_rules_name'] = 'required|min:1|max:300|unique:rules_warehouse_allocation,trouble_rules_name,:id,id|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u';
            $rules = json_decode(str_replace(":id",$trouble_id,json_encode($rules)),true);
        }
        return $rules;
    }

    public static function getAllocationConditionsMessages()
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

    public static function getAllocationConditionsAttributes()
    {
        return [
            'trouble_rules_name' => '规则名称',
            'opening_status' => '是否启用',
        ];
    }
}
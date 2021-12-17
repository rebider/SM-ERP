<?php

namespace App\Validates;


class LogisticsMappingValidate
{

    public static function getLogisticsMappingRules()
    {
        return [
            'plat_logistic_name' => 'required|min:1|max:30',
            'logistic_name' => 'required|min:1|max:30',
            'carrier_name' => 'required|min:1|max:30',
            'logistic_id' => 'required|integer',
            'plat_id' => 'required|integer',
        ];
    }

    public static function getLogisticsMappingMessages()
    {
        return [
            'required' => ':attribute不能为空',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度最大:max位',
            'min' => ':attribute长度最小:min位',
        ];
    }

    public static function getLogisticsMappingAttributes()
    {
        return [
            'logistic_id' => '系统物流',
            'logistic_name' => '系统物流',
            'carrier_name' => '电商物流承运商',
            'plat_logistic_name' => '电商物流名称',
            'plat_id' => '平台',
        ];
    }
}
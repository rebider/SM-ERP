<?php

namespace App\Validates;


class TicketValidate
{
    //确认归档验证规则
    public static function getRulesConfirmArchive()
    {
        return [
            'ticket_id' => 'required|integer|exists:ticket,ticket_id',
            'estimate_damages' => 'required|max:7|regex:/^\d+(.\d{1,2})?$/u',
            'real_damages' => 'required|max:7|regex:/^\d+(.\d{1,2})?$/u',
            'bcckup_remark' => 'max:500'
        ];
    }

    public static function getMessages()
    {
        return [
            'required' => ':attribute不能为空',
            'integer' => ':attribute必须是数值',
            'max' => ':attribute长度最大:max位',
            'exists' => ':attribute不存在',
            'regex' => ':attribute格式不正确'
        ];
    }

    public static function getAttributes()
    {
        return [
            'ticket_id' => '工单',
            'estimate_damages' => '涉及赔偿金额',
            'real_damages' => '实际赔偿金额',
            'bcckup_remark' => '归档备注',
        ];
    }
}
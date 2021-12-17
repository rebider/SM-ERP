<?php
namespace App\Auth\Common\Enums;


/**
 * Class 账号类型
 * @package App\Auth\Common\Enums
 */
class AccountType
{
    /**
     * @var 超级管理员
     */
    const ADMIN = 1;

    /**
     * @var 主账号
     */
    const PRIMARY = 2;

    /**
     * @var 子账号
     */
    const CHILDREN = 3;
}
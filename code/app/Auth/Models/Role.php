<?php

namespace App\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	/**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * 与模型关联的数据表主键
     *
     * @var int
     */
    protected $primaryKey = 'id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var 后台管理员权限
     */
    const ADMIN_ROLE = 1;

    /**
     * @var 主账户权限
     */
    const PRIMARY_ROLE = 2;

    /**
     * @var 启用
     */
    const ROLE_ENABLE_STATE = 1;

    /**
     * @var 禁用
     */
    const ROLE_DISABLED_STATE = 0;

}


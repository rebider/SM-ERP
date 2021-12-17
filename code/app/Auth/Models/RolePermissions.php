<?php

namespace App\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
	/**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'role_permissions';

    /**
     * 与模型关联的数据表主键
     *
     * @var int
     */
    protected $primaryKey = 'role_permissions_id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden  = [
        'created_at',
        'updated_at'
    ];
}


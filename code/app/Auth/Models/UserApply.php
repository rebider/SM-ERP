<?php

namespace App\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class UserApply extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'user_apply';

    /**
     * 与模型关联的数据表主键
     *
     * @var int
     */
    protected $primaryKey = 'user_apply_id';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}


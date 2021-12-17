<?php

namespace App\Models;

use App\Auth\Models\Users;
use Illuminate\Database\Eloquent\Model;

class ExportRecode extends Model
{
    protected $table = 'export_recode';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','action_type','fields_id','created_at','updated_at'];

    /**
     * @var 订单导出
     */
    const EXPORT_ORDERS = 1;
    /**
     * @var 配货单导出
     */
    const EXPORT_PICKING = 2;

    /**
     * @return $this
     * Note: 用户模型
     * Data: 2019/3/7 11:16
     * Author: zt7785
     */
    public function Users()
    {
        return $this->belongsTo(Users::class, 'created_man', 'user_id')->select(['user_id', 'created_man', 'username', 'user_code', 'state', 'user_type']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class RulesOrderSplitMerge
 * Notes: 平台拆单|合单
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class RulesOrderSplitMerge extends Model
{
    protected $table = 'rules_order_split_merge';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','user_id','type','status','created_at','updated_at'];
    /**
     * 亚马逊
     */
    const AMAZON_TYPE = 1;

    const RAKUTEN_TYPE = 3;

    const OTHER_TYPE = 4;
    /**
     * @var 启用
     */
    const STATUS_ON = 1;
    /**
     * @var 禁用
     */
    const STATUS_DIS = 2;

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

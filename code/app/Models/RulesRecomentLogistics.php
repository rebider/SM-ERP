<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class RulesRecomentLogistics
 * Notes: 物流推荐
 * @package App\Models
 * Data: 2019/3/7 15:21
 * Author: zt7785
 */
class RulesRecomentLogistics extends Model
{
    protected $table = 'rules_recoment_logistics';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','logistic_rule_id','logistics_id','score','created_at','updated_at'];

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

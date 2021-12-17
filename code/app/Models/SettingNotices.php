<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class SettingNotices
 * Notes: 公告管理
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class SettingNotices extends Model
{
    const ON_STATUS = 1;//显示
    const OFF_STATUS = 2;//隐藏

    const ON_IMPORTANT = 0;//不重要
    const OFF_IMPORTANT = 1;//重要

    protected $table = 'setting_notices';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','title','content','status','important','created_at','updated_at'];


    public function getContentAttribute($value){
        return htmlspecialchars_decode($value);
    }


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
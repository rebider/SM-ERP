<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Models\Users;

/**
 * Class SettingLogisticsWarehouses
 * Notes: 物流仓库表
 * @package App\Models
 * Data: 2019/3/7 15:19
 * Author: zt7785
 */
class SettingLogisticsWarehouses extends Model
{
    protected $table = 'setting_logistics_warehouses';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','warehouse_id','logistic_id','created_at','updated_at'];

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

    public static function postSetLogicWareHouse($wareArr,$logisRe,$id = 0,$user_id){
       if(!empty($wareArr)){
           foreach($wareArr as $k=>$v){
               if($id){
                   $re = self::update([
                       'user_id'=>$user_id,
                       'created_man'=>$user_id,//todo Auth
                       'logistic_id'=>$logisRe,
                       'warehouse_id'=>$v,
                       'updated_at'=>date('Y-m-d H:i:s')
                   ]);
               }else{
                   $re = self::insert([
                       'user_id'=>$user_id,
                       'created_man'=>$user_id,//todo Auth
                       'logistic_id'=>$logisRe,
                       'warehouse_id'=>$v,
                       'created_at'=>date('Y-m-d H:i:s'),
                       'updated_at'=>date('Y-m-d H:i:s')
                   ]);
               }
               if(!$re){
                   return false;
               }
           }
           return $re;
       }
       return false;
    }

    public static function postSlwh($data,$id=0){
        return self::updateOrCreate(['id'=>$id],$data);
    }

    /**
     * @note
     * 物流仓库中间表 orm
     * @since: 2019/4/10
     * @author: zt7837
     * @param:
     * @return: array
     */
    public static function logisticWareInsert($logicWareModel,$param,$edit = false){
        if($edit){
            $logicWareModel->updated_at = date('Y-m-d H:i:s');
            $logicWareModel->save();
        }else{
            $logicWareModel->created_man = $param['created_man'];
            $logicWareModel->user_id = $param['user_id'];
            $logicWareModel->logistic_id = $param['logistic_id'];
            $logicWareModel->warehouse_id = $param['warehouse_id'];
            $logicWareModel->created_at = date('Y-m-d H:i:s');
            $logicWareModel->updated_at = date('Y-m-d H:i:s');
            $logicWareModel->save();
        }

        return $logicWareModel;
    }


}

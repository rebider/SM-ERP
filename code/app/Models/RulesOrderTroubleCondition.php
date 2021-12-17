<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RulesOrderTroubleCondition
 * Notes: 订单问题规则条件 基础设置表
 * @package App\Models
 * Data: 2019/3/14 11:32
 * Author: zt7785
 */
class RulesOrderTroubleCondition extends Model
{
    protected $table = 'rules_condition';

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','condition_type','dialog_type','condition_prefix','condition_name','condition_sql','is_show','remark','related','relid','sertid','input_field','created_at','updated_at'];

    /**
     * condition_type 条件类型 1:订单来源 2:物流信息 3:商品信息 4:发货信息
     * dialog_type 弹窗类型 1:指定平台 2:指定店铺 3:指定国家 4:指定邮编 5:指定字段为空 6:指定商品尺寸区间 7:指定商品重量 8:指定商品属性 9:指定商品sku 10:指定商品总金额区间 11:指定数量区间 12:指定仓库 13:指定物流
     */

    /**
     * @return array
     * Note: 获取所有条件 列表渲染
     * Data: 2019/3/14 13:37
     * Author: zt7785
     */
    public static function getConditionsData ($param = [])
    {
        $collection = self::select(['id','condition_type','dialog_type','condition_prefix','condition_name','condition_sql','is_show','related','relid','input_field','sertid']);
        isset($param['is_show']) && $collection->where('is_show',$param['is_show']);
        $result = $collection->orderBy('id','ASC')->get()->toArray();
        return $result;
    }

}
